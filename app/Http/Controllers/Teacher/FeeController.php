<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\ClassStudent;
use App\Models\Payment;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FeeController extends Controller
{
    /**
     * Display fee tracking list with filters.
     */
    public function index(Request $request): View
    {
        $teacherId = Auth::user()->effectiveTeacherId();
        $classes = ClassRoom::where('teacher_id', $teacherId)->get();
        $classIds = $classes->pluck('id')->toArray();

        $selectedClassId = $request->query('class_id');
        $selectedMonth = $request->query('month', Carbon::now()->format('Y-m'));
        $selectedStatus = $request->query('status', 'all'); // 'all', 'paid', 'pending'

        $targetClassIds = $selectedClassId ? [(int) $selectedClassId] : $classIds;

        // Query active enrollments for target classes
        $enrollmentsQuery = ClassStudent::with(['classRoom', 'student'])
            ->whereIn('class_id', $targetClassIds)
            ->where('status', 'active');

        $enrollments = $enrollmentsQuery->get();

        // Get all payments for these classes in the selected month
        $payments = Payment::whereIn('class_id', $targetClassIds)
            ->where('payment_month', $selectedMonth)
            ->get()
            ->keyBy(function ($p) {
                return $p->class_id.'_'.$p->student_id;
            });

        // Compute students with paid/pending status
        $feeRecords = $enrollments->map(function ($enrollment) use ($payments, $selectedMonth) {
            $key = $enrollment->class_id.'_'.$enrollment->student_id;
            $payment = $payments->get($key);

            return (object) [
                'enrollment' => $enrollment,
                'class' => $enrollment->classRoom,
                'student' => $enrollment->student,
                'monthly_fee' => $enrollment->classRoom->monthly_fee,
                'month' => $selectedMonth,
                'is_paid' => ! is_null($payment),
                'payment' => $payment,
            ];
        });

        // Apply status filter if needed
        if ($selectedStatus === 'paid') {
            $feeRecords = $feeRecords->where('is_paid', true);
        } elseif ($selectedStatus === 'pending') {
            $feeRecords = $feeRecords->where('is_paid', false);
        }

        // Summary figures
        $totalPotential = $enrollments->sum(fn ($e) => $e->classRoom->monthly_fee);
        $totalCollected = $payments->sum('amount');
        $paidCount = $payments->count();
        $pendingCount = max(0, $enrollments->count() - $paidCount);

        // Recent 10 recorded payments
        $recentPayments = Payment::with(['student', 'classRoom'])
            ->where('teacher_id', $teacherId)
            ->latest()
            ->limit(10)
            ->get();

        return view('teacher.fees.index', [
            'classes' => $classes,
            'selectedClassId' => $selectedClassId,
            'selectedMonth' => $selectedMonth,
            'selectedStatus' => $selectedStatus,
            'feeRecords' => $feeRecords,
            'totalPotential' => $totalPotential,
            'totalCollected' => $totalCollected,
            'paidCount' => $paidCount,
            'pendingCount' => $pendingCount,
            'recentPayments' => $recentPayments,
            'enrollments' => $enrollments,
        ]);
    }

    /**
     * Save cash payment.
     * Simple fee logging modal: Select Student -> Choose Billing Month -> Enter Amount -> Save.
     */
    public function collect(Request $request): RedirectResponse
    {
        $paymentType = $request->input('payment_type', 'monthly');

        $validated = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'student_id' => ['required', 'exists:students,id'],
            'payment_type' => ['nullable', 'in:monthly,daily'],
            'payment_month' => [$paymentType === 'daily' ? 'nullable' : 'required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'amount' => ['required', 'numeric', 'min:0'],
            'paid_date' => ['nullable', 'date'],
            'payment_method' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $teacherId = Auth::user()->effectiveTeacherId();
        $class = ClassRoom::findOrFail($validated['class_id']);
        if ($class->teacher_id !== $teacherId && ! Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $paidDate = $validated['paid_date'] ?: Carbon::today()->toDateString();
        $paymentMonth = $paymentType === 'daily'
            ? Carbon::parse($paidDate)->format('Y-m')
            : $validated['payment_month'];

        // If monthly payment, check if student already paid for this specific month
        if ($paymentType === 'monthly') {
            $existing = Payment::where('class_id', $class->id)
                ->where('student_id', $validated['student_id'])
                ->where('payment_type', 'monthly')
                ->where('payment_month', $paymentMonth)
                ->first();

            if ($existing) {
                return back()->with('error', "Fee has already been recorded for this student for month {$paymentMonth} (Receipt #{$existing->receipt_no}).");
            }
        }

        $receiptNo = Payment::generateReceiptNumber();

        $payment = Payment::create([
            'class_id' => $class->id,
            'student_id' => $validated['student_id'],
            'teacher_id' => $teacherId,
            'collected_by' => Auth::id(),
            'payment_type' => $paymentType,
            'amount' => $validated['amount'],
            'payment_month' => $paymentMonth,
            'paid_date' => $paidDate,
            'receipt_no' => $receiptNo,
            'payment_method' => $validated['payment_method'] ?: 'cash',
            'notes' => $validated['notes'] ?: ($paymentType === 'daily' ? "Daily session fee on {$paidDate}" : null),
        ]);

        return redirect()->route('teacher.fees.receipt', $payment)
            ->with('success', "Cash payment of LKR {$payment->amount} recorded! Receipt: {$payment->receipt_no}");
    }

    /**
     * Printable Receipt and SMS / WhatsApp dispatch view.
     */
    public function receipt(Payment $payment): View
    {
        $payment->load(['classRoom.teacher', 'student']);

        // Format pre-filled WhatsApp & SMS message
        $studentName = $payment->student->full_name;
        $className = $payment->classRoom->name;
        $month = Carbon::createFromFormat('Y-m', $payment->payment_month)->format('F Y');
        $amount = number_format($payment->amount, 2);
        $receiptNo = $payment->receipt_no;
        $teacherName = $payment->classRoom->teacher->name;

        $messageText = "Payment Receipt Notification:\n"
            ."Dear Parent/Guardian,\n"
            ."We have received {$amount} cash fee for {$studentName} ({$className}) for {$month}.\n"
            ."Receipt No: {$receiptNo}\n"
            ."Instructor: {$teacherName}\n"
            .'Thank you!';

        // Clean guardian phone for WhatsApp link
        $guardianPhoneClean = preg_replace('/[^0-9]/', '', $payment->student->guardian_phone);
        $whatsAppUrl = "https://wa.me/{$guardianPhoneClean}?text=".urlencode($messageText);

        return view('teacher.fees.receipt', [
            'payment' => $payment,
            'whatsAppUrl' => $whatsAppUrl,
            'messageText' => $messageText,
        ]);
    }

    /**
     * Delete or void a payment.
     */
    public function destroy(Payment $payment): RedirectResponse
    {
        if ($payment->teacher_id !== Auth::user()->effectiveTeacherId() && ! Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $receiptNo = $payment->receipt_no;
        $payment->delete();

        return back()->with('info', "Payment {$receiptNo} has been voided/deleted.");
    }
}
