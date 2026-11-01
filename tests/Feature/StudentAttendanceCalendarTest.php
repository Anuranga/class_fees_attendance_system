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
use Tests\TestCase;

class StudentAttendanceCalendarTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;

    protected ClassRoom $classRoom;

    protected Student $student;

    protected ClassStudent $enrollment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = User::create([
            'name' => 'Prof. Charles Babbage',
            'email' => 'babbage@edutrack.test',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'status' => 'active',
        ]);

        $this->classRoom = ClassRoom::create([
            'teacher_id' => $this->teacher->id,
            'name' => 'Advanced Physics 2026',
            'grade_level' => 'Grade 12',
            'monthly_fee' => 4500.00,
            'registration_token' => 'physics-token-2026',
            'is_registration_open' => true,
        ]);

        $this->student = Student::create([
            'admission_number' => 'ADM-00101',
            'full_name' => 'Ada Lovelace',
            'gender' => 'female',
            'contact_number' => '+94 77 123 4567',
            'guardian_name' => 'Lord Byron',
            'guardian_phone' => '+94 77 987 6543',
        ]);

        $this->enrollment = ClassStudent::create([
            'class_id' => $this->classRoom->id,
            'student_id' => $this->student->id,
            'student_class_uid' => 'CLS-1-STD-0101',
            'qr_token' => 'qr-token-ada-lovelace',
            'joined_via' => 'manual',
            'status' => 'active',
        ]);
    }

    public function test_teacher_can_view_student_attendance_calendar_full_page(): void
    {
        $response = $this->actingAs($this->teacher)->get(
            route('teacher.classes.students.attendance', [$this->classRoom, $this->student])
        );

        $response->assertOk();
        $response->assertSee('Ada Lovelace');
        $response->assertSee('Advanced Physics 2026');
        $response->assertSee('Monthly Attendance Calendar');
        $response->assertSee('Attendance Log History');
        $response->assertSee('CLS-1-STD-0101');
    }

    public function test_teacher_can_fetch_student_attendance_calendar_via_ajax(): void
    {
        $currentMonth = Carbon::now()->format('Y-m');

        $response = $this->actingAs($this->teacher)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->get(route('teacher.classes.students.attendance', [
                $this->classRoom,
                $this->student,
                'month' => $currentMonth,
            ]));

        $response->assertOk();
        $response->assertJsonStructure([
            'html',
            'student_name',
            'month_label',
        ]);
        $response->assertJson([
            'student_name' => 'Ada Lovelace',
        ]);

        $this->assertStringContainsString('Monthly Attendance Calendar', $response->json('html'));
        $this->assertStringContainsString('Ada Lovelace', $response->json('html'));
    }

    public function test_calendar_displays_present_late_and_missed_status_accurately(): void
    {
        $month = Carbon::now()->format('Y-m');
        $date1 = Carbon::now()->startOfMonth()->addDays(2)->toDateString();
        $date2 = Carbon::now()->startOfMonth()->addDays(5)->toDateString();
        $date3 = Carbon::now()->startOfMonth()->addDays(8)->toDateString();

        // 1. Student attended date1 on time (Present)
        Attendance::create([
            'class_id' => $this->classRoom->id,
            'student_id' => $this->student->id,
            'date' => $date1,
            'check_in_time' => '08:15:00',
            'status' => 'present',
            'tute_provided' => true,
            'tute_name' => 'Kinematics Chapter 1',
            'marked_by' => $this->teacher->id,
        ]);

        // 2. Student attended date2 late
        Attendance::create([
            'class_id' => $this->classRoom->id,
            'student_id' => $this->student->id,
            'date' => $date2,
            'check_in_time' => '09:05:00',
            'status' => 'late',
            'tute_provided' => false,
            'marked_by' => $this->teacher->id,
        ]);

        // 3. Another student in this class attended on date3 (meaning a class session was held on date3, but Ada missed it)
        $anotherStudent = Student::create([
            'admission_number' => 'ADM-00102',
            'full_name' => 'Michael Faraday',
            'gender' => 'male',
            'contact_number' => '+94 77 222 3333',
            'guardian_name' => 'Mrs. Faraday',
            'guardian_phone' => '+94 77 444 5555',
        ]);
        ClassStudent::create([
            'class_id' => $this->classRoom->id,
            'student_id' => $anotherStudent->id,
            'student_class_uid' => 'CLS-1-STD-0102',
            'qr_token' => 'qr-token-faraday',
            'joined_via' => 'manual',
            'status' => 'active',
        ]);
        Attendance::create([
            'class_id' => $this->classRoom->id,
            'student_id' => $anotherStudent->id,
            'date' => $date3,
            'check_in_time' => '08:20:00',
            'status' => 'present',
            'marked_by' => $this->teacher->id,
        ]);

        $response = $this->actingAs($this->teacher)->get(
            route('teacher.classes.students.attendance', [$this->classRoom, $this->student, 'month' => $month])
        );

        $response->assertOk();
        // Check statistics: 3 sessions total, 1 present, 1 late, 1 missed (66.7% rate)
        $response->assertSee('Kinematics Chapter 1');
        $response->assertSee('08:15 AM');
        $response->assertSee('09:05 AM');
        $response->assertSee('66.7%');
    }

    public function test_calendar_displays_monthly_fee_status(): void
    {
        $month = Carbon::now()->format('Y-m');

        Payment::create([
            'teacher_id' => $this->teacher->id,
            'class_id' => $this->classRoom->id,
            'student_id' => $this->student->id,
            'payment_month' => $month,
            'amount' => 4500.00,
            'paid_date' => Carbon::now()->toDateString(),
            'status' => 'paid',
            'receipt_no' => 'REC-ADA-2026-99',
            'collected_by' => $this->teacher->id,
            'fee_type' => 'monthly',
        ]);

        $response = $this->actingAs($this->teacher)->get(
            route('teacher.classes.students.attendance', [$this->classRoom, $this->student, 'month' => $month])
        );

        $response->assertOk();
        $response->assertSee('REC-ADA-2026-99');
        $response->assertSee('Paid');
    }

    public function test_unauthorized_teacher_cannot_view_another_teachers_student_attendance(): void
    {
        $otherTeacher = User::create([
            'name' => 'Prof. James Maxwell',
            'email' => 'maxwell@edutrack.test',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'status' => 'active',
        ]);

        $response = $this->actingAs($otherTeacher)->get(
            route('teacher.classes.students.attendance', [$this->classRoom, $this->student])
        );

        $response->assertForbidden();
    }

    public function test_cannot_view_attendance_for_student_not_enrolled_in_class(): void
    {
        $unenrolledStudent = Student::create([
            'admission_number' => 'ADM-00999',
            'full_name' => 'Nikola Tesla',
            'gender' => 'male',
            'contact_number' => '+94 77 999 0000',
            'guardian_name' => 'Milutin Tesla',
            'guardian_phone' => '+94 77 999 1111',
        ]);

        $response = $this->actingAs($this->teacher)->get(
            route('teacher.classes.students.attendance', [$this->classRoom, $unenrolledStudent])
        );

        $response->assertNotFound();
    }
}
