<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\ClassStudent;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AttendanceAndFeeTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;

    protected ClassRoom $classRoom;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = User::create([
            'name' => 'Prof. Alan Turing',
            'email' => 'teacher@edutrack.test',
            'password' => Hash::make('password'),
            'role' => 'teacher',
        ]);

        $this->classRoom = ClassRoom::create([
            'teacher_id' => $this->teacher->id,
            'name' => 'Advanced Pure Mathematics',
            'grade_level' => 'Grade 12',
            'monthly_fee' => 3500.00,
            'registration_token' => 'math12-token-2026',
            'is_registration_open' => true,
        ]);
    }

    public function test_student_can_self_register_via_public_class_link(): void
    {
        $postData = [
            'full_name' => 'Test Self Registered Student',
            'contact_number' => '+1 555-9988',
            'guardian_name' => 'Test Parent',
            'guardian_phone' => '+1 555-9977',
        ];

        $response = $this->post(route('public.join.process', $this->classRoom->registration_token), $postData);

        $this->assertDatabaseHas('students', [
            'full_name' => 'Test Self Registered Student',
            'contact_number' => '+1 555-9988',
        ]);

        $student = Student::where('contact_number', '+1 555-9988')->first();
        $this->assertNotNull($student);

        $enrollment = ClassStudent::where('class_id', $this->classRoom->id)
            ->where('student_id', $student->id)
            ->first();

        $this->assertNotNull($enrollment);
        $this->assertEquals('link', $enrollment->joined_via);
        $this->assertNotNull($enrollment->qr_token);
        $this->assertNotNull($enrollment->student_class_uid);

        $response->assertRedirect(route('public.pass', $enrollment->qr_token));

        // Test pass view loads successfully
        $passResponse = $this->get(route('public.pass', $enrollment->qr_token));
        $passResponse->assertStatus(200);
        $passResponse->assertSee('Test Self Registered Student');
        $passResponse->assertSee($enrollment->student_class_uid);
    }

    public function test_qr_scanner_marks_attendance_and_prevents_duplicate_scan(): void
    {
        // Create an enrolled student
        $student = Student::create([
            'admission_number' => 'ADM-99999',
            'full_name' => 'Scanner Test Student',
            'contact_number' => '+1 555-7766',
            'guardian_name' => 'Guardian Test',
            'guardian_phone' => '+1 555-7755',
        ]);

        $enrollment = ClassStudent::create([
            'class_id' => $this->classRoom->id,
            'student_id' => $student->id,
            'student_class_uid' => 'CLS-'.$this->classRoom->id.'-STD-99999',
            'qr_token' => 'unique-test-qr-token-12345',
            'joined_via' => 'manual',
            'status' => 'active',
        ]);

        // 1. First scan should succeed (200 OK)
        $scanResponse = $this->actingAs($this->teacher)->postJson(route('teacher.attendance.scan', $this->classRoom), [
            'qr_token' => $enrollment->qr_token,
        ]);

        $scanResponse->assertStatus(200);
        $scanResponse->assertJson([
            'success' => true,
            'status' => 'marked',
        ]);

        $this->assertDatabaseHas('attendances', [
            'class_id' => $this->classRoom->id,
            'student_id' => $student->id,
        ]);
        $this->assertEquals(Carbon::today()->toDateString(), Attendance::where('class_id', $this->classRoom->id)->where('student_id', $student->id)->first()->date->toDateString());

        // 2. Duplicate scan today should return 409 Conflict
        $duplicateResponse = $this->actingAs($this->teacher)->postJson(route('teacher.attendance.scan', $this->classRoom), [
            'qr_token' => $enrollment->qr_token,
        ]);

        $duplicateResponse->assertStatus(409);
        $duplicateResponse->assertJson([
            'success' => false,
            'status' => 'duplicate',
        ]);
    }

    public function test_cash_fee_collection_and_receipt_generation(): void
    {
        $student = Student::create([
            'admission_number' => 'ADM-88888',
            'full_name' => 'Fee Test Student',
            'contact_number' => '+1 555-8888',
            'guardian_name' => 'Fee Guardian',
            'guardian_phone' => '+1 555-8877',
        ]);

        ClassStudent::create([
            'class_id' => $this->classRoom->id,
            'student_id' => $student->id,
            'student_class_uid' => 'CLS-'.$this->classRoom->id.'-STD-88888',
            'qr_token' => (string) Str::uuid(),
            'joined_via' => 'manual',
            'status' => 'active',
        ]);

        $month = '2026-11';

        $collectData = [
            'class_id' => $this->classRoom->id,
            'student_id' => $student->id,
            'payment_month' => $month,
            'amount' => $this->classRoom->monthly_fee,
            'paid_date' => Carbon::today()->toDateString(),
            'payment_method' => 'cash',
            'notes' => 'Test cash collection',
        ];

        $response = $this->actingAs($this->teacher)->post(route('teacher.fees.collect'), $collectData);

        $payment = Payment::where('class_id', $this->classRoom->id)
            ->where('student_id', $student->id)
            ->where('payment_month', $month)
            ->first();

        $this->assertNotNull($payment);
        $this->assertStringStartsWith('RCP-', $payment->receipt_no);

        $response->assertRedirect(route('teacher.fees.receipt', $payment));

        // Test receipt view
        $receiptResponse = $this->actingAs($this->teacher)->get(route('teacher.fees.receipt', $payment));
        $receiptResponse->assertStatus(200);
        $receiptResponse->assertSee($payment->receipt_no);
        $receiptResponse->assertSee('Send WhatsApp Receipt');
    }
}
