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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
    }

    public function test_unified_attendance_and_fee_marking(): void
    {
        $student = Student::create([
            'admission_number' => 'ADM-77777',
            'full_name' => 'Unified Checkin Student',
            'contact_number' => '+1 555-4433',
            'guardian_name' => 'Guardian Unified',
            'guardian_phone' => '+1 555-4422',
        ]);

        ClassStudent::create([
            'class_id' => $this->classRoom->id,
            'student_id' => $student->id,
            'student_class_uid' => 'CLS-'.$this->classRoom->id.'-STD-77777',
            'qr_token' => (string) Str::uuid(),
            'joined_via' => 'manual',
            'status' => 'active',
        ]);

        // Post to mark-with-fee route: Attendance is compulsory, collect_fee is true for last month
        $previousMonth = Carbon::now()->subMonth()->format('Y-m');
        $postData = [
            'student_id' => $student->id,
            'date' => Carbon::today()->toDateString(),
            'status' => 'present',
            'tute_provided' => '1',
            'collect_fee' => '1',
            'payment_type' => 'monthly',
            'payment_month' => $previousMonth,
            'amount' => 3500.00,
            'payment_method' => 'cash',
            'notes' => 'Paid for previous month during check-in',
        ];

        $response = $this->actingAs($this->teacher)
            ->post(route('teacher.attendance.mark-with-fee', $this->classRoom), $postData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify attendance record
        $this->assertDatabaseHas('attendances', [
            'class_id' => $this->classRoom->id,
            'student_id' => $student->id,
            'status' => 'present',
            'tute_provided' => true,
        ]);

        // Verify payment record with previous month
        $this->assertDatabaseHas('payments', [
            'class_id' => $this->classRoom->id,
            'student_id' => $student->id,
            'payment_month' => $previousMonth,
            'amount' => 3500.00,
            'payment_method' => 'cash',
        ]);
    }

    public function test_teacher_can_view_daily_sheet_and_cash_collections(): void
    {
        $response = $this->actingAs($this->teacher)->get(route('teacher.daily.sheet', [
            'date' => Carbon::today()->toDateString(),
            'class_id' => $this->classRoom->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Daily Attendance & Cash Ledger');
        $response->assertSee('Total Cash Collected');
        $response->assertSee($this->classRoom->name);
    }

    public function test_student_gender_avatar_fallbacks_when_no_photo_uploaded(): void
    {
        // 1. Female student self-registration
        $this->post(route('public.join.process', $this->classRoom->registration_token), [
            'full_name' => 'Alice Female Student',
            'gender' => 'female',
            'contact_number' => '+1 555-1111',
            'guardian_name' => 'Alice Parent',
            'guardian_phone' => '+1 555-2222',
        ]);

        $alice = Student::where('contact_number', '+1 555-1111')->first();
        $this->assertNotNull($alice);
        $this->assertEquals('female', $alice->gender);
        $this->assertNull($alice->photo_path);
        $this->assertStringContainsString('images/avatars/female.svg', $alice->avatar_url);

        // 2. Male student self-registration
        $this->post(route('public.join.process', $this->classRoom->registration_token), [
            'full_name' => 'Bob Male Student',
            'gender' => 'male',
            'contact_number' => '+1 555-3333',
            'guardian_name' => 'Bob Parent',
            'guardian_phone' => '+1 555-4444',
        ]);

        $bob = Student::where('contact_number', '+1 555-3333')->first();
        $this->assertNotNull($bob);
        $this->assertEquals('male', $bob->gender);
        $this->assertNull($bob->photo_path);
        $this->assertStringContainsString('images/avatars/male.svg', $bob->avatar_url);
    }

    public function test_student_manual_enrollment_with_photo_upload_stores_image_and_returns_in_scan(): void
    {
        Storage::fake('public');

        $photo = UploadedFile::fake()->image('student_profile.jpg', 200, 200);

        $response = $this->actingAs($this->teacher)
            ->post(route('teacher.classes.students.manual', $this->classRoom), [
                'mode' => 'new',
                'full_name' => 'Photo Enrolled Student',
                'gender' => 'female',
                'photo' => $photo,
                'contact_number' => '+1 555-5555',
                'guardian_name' => 'Guardian Photo',
                'guardian_phone' => '+1 555-6666',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $student = Student::where('contact_number', '+1 555-5555')->first();
        $this->assertNotNull($student);
        $this->assertEquals('female', $student->gender);
        $this->assertNotNull($student->photo_path);

        Storage::disk('public')->assertExists($student->photo_path);
        $this->assertStringContainsString($student->photo_path, $student->avatar_url);

        $enrollment = ClassStudent::where('class_id', $this->classRoom->id)
            ->where('student_id', $student->id)
            ->first();

        // Scan QR code and verify avatar_url is in response
        $scanResponse = $this->actingAs($this->teacher)->postJson(route('teacher.attendance.scan', $this->classRoom), [
            'qr_token' => $enrollment->qr_token,
        ]);

        $scanResponse->assertStatus(200);
        $scanResponse->assertJsonPath('student.avatar_url', $student->avatar_url);
    }
}
