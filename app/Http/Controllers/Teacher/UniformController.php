<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\UniformItem;
use App\Models\UniformOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UniformController extends Controller
{
    /**
     * Display uniform orders list and payment/delivery status.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        if (! $user->canManageUniforms()) {
            abort(403, 'Unauthorized. You do not have permission to manage uniforms and merchandise.');
        }

        $classes = $user->accessibleClassesQuery()->get();
        $classIds = $classes->pluck('id')->toArray();

        $selectedClassId = $request->query('class_id');
        $paymentStatus = $request->query('payment_status', 'all');
        $deliveryStatus = $request->query('delivery_status', 'all');

        $query = UniformOrder::with(['student', 'classRoom', 'collector', 'deliverer'])
            ->where(function ($q) use ($classIds) {
                $q->whereIn('class_id', $classIds)->orWhereNull('class_id');
            })
            ->latest();

        if ($selectedClassId) {
            $query->where('class_id', $selectedClassId);
        }

        if ($paymentStatus !== 'all') {
            $query->where('payment_status', $paymentStatus);
        }

        if ($deliveryStatus !== 'all') {
            $query->where('delivery_status', $deliveryStatus);
        }

        $orders = $query->paginate(20);
        $school = SchoolSetting::get();
        $items = UniformItem::where('is_active', true)
            ->where(function ($q) use ($school) {
                $q->where('school_setting_id', $school->id)->orWhereNull('school_setting_id');
            })->get();

        // Students in accessible classes for creating orders
        $students = Student::whereHas('classes', function ($q) use ($classIds) {
            $q->whereIn('classes.id', $classIds);
        })->orderBy('full_name')->get();

        $summary = [
            'total_orders' => UniformOrder::whereIn('class_id', $classIds)->count(),
            'paid_amount' => UniformOrder::whereIn('class_id', $classIds)->where('payment_status', 'paid')->sum('total_amount'),
            'pending_amount' => UniformOrder::whereIn('class_id', $classIds)->where('payment_status', 'pending')->sum('total_amount'),
            'ready_for_delivery' => UniformOrder::whereIn('class_id', $classIds)->where('delivery_status', 'ready')->count(),
            'delivered' => UniformOrder::whereIn('class_id', $classIds)->where('delivery_status', 'delivered')->count(),
        ];

        return view('teacher.uniforms.index', compact('orders', 'classes', 'selectedClassId', 'paymentStatus', 'deliveryStatus', 'school', 'items', 'students', 'summary'));
    }

    /**
     * Store a new uniform order for a student.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (! $user->canManageUniforms()) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'uniform_item_id' => ['nullable', 'exists:uniform_items,id'],
            'item_name' => ['required', 'string', 'max:255'],
            'size' => ['nullable', 'string', 'max:30'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
            'mark_as_paid' => ['nullable', 'boolean'],
        ]);

        $student = Student::with('classes')->findOrFail($validated['student_id']);
        $classId = $student->classes->first()?->id;
        $total = $validated['unit_price'] * $validated['quantity'];

        $isPaid = $request->boolean('mark_as_paid');
        $receiptNo = $isPaid ? ('RCP-UNF-'.date('Ym').'-'.strtoupper(Str::random(5))) : null;

        $order = UniformOrder::create([
            'order_number' => UniformOrder::generateOrderNumber(),
            'student_id' => $student->id,
            'class_id' => $classId,
            'uniform_item_id' => $validated['uniform_item_id'] ?? null,
            'item_name' => $validated['item_name'],
            'size' => $validated['size'] ?? null,
            'quantity' => $validated['quantity'],
            'unit_price' => $validated['unit_price'],
            'total_amount' => $total,
            'payment_status' => $isPaid ? 'paid' : 'pending',
            'paid_date' => $isPaid ? now()->toDateString() : null,
            'receipt_no' => $receiptNo,
            'collected_by' => $isPaid ? $user->id : null,
            'delivery_status' => 'ordered',
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', "Uniform Order #{$order->order_number} recorded for {$student->full_name}!");
    }

    /**
     * Mark uniform payment as collected and prepare Sinhala WhatsApp notification.
     */
    public function markPayment(Request $request, UniformOrder $order): RedirectResponse
    {
        $user = Auth::user();
        if (! $user->canManageUniforms()) {
            abort(403, 'Unauthorized.');
        }

        $receiptNo = 'RCP-UNF-'.date('Ym').'-'.strtoupper(Str::random(5));

        $order->update([
            'payment_status' => 'paid',
            'paid_date' => now()->toDateString(),
            'receipt_no' => $receiptNo,
            'collected_by' => $user->id,
        ]);

        return back()->with('success', "Uniform payment of LKR {$order->total_amount} received! Receipt: {$receiptNo}. Sinhala WhatsApp message is ready.");
    }

    /**
     * Mark uniform as Delivered / Handed over to child or parent.
     */
    public function markDelivery(Request $request, UniformOrder $order): RedirectResponse
    {
        $user = Auth::user();
        if (! $user->canManageUniforms()) {
            abort(403, 'Unauthorized.');
        }

        $order->update([
            'delivery_status' => 'delivered',
            'delivered_date' => now()->toDateString(),
            'delivered_by' => $user->id,
        ]);

        return back()->with('success', "Uniform for {$order->student->full_name} marked as Delivered! Delivery WhatsApp message generated.");
    }

    /**
     * Save uniform catalog item (by Principal).
     */
    public function storeItem(Request $request): RedirectResponse
    {
        if (! Auth::user()->isAdmin()) {
            abort(403, 'Only the Principal can add uniform catalog items.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'size_options' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $school = SchoolSetting::get();

        UniformItem::create([
            'school_setting_id' => $school->id,
            'name' => $validated['name'],
            'size_options' => $validated['size_options'] ?? null,
            'price' => $validated['price'],
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        return back()->with('success', "Uniform item '{$validated['name']}' added to school catalog!");
    }
}
