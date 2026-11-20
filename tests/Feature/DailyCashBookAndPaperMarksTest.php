<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\ClassStudent;
use App\Models\Paper;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DailyCashBookAndPaperMarksTest extends TestCase
{
    use RefreshDatabase;

    private function createTeacher(string $email = 'teacher@test.lk'): User
    {
        return User::create([
            'name' => 'Mr. Perera',
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => 'teacher',
            'status' => 'active',
        ]);
    }

    private function createStudent(string $admissionNumber, string $fullName, string $gender = 'male'): Student
    {
        return Student::create([
            'admission_number' => $admissionNumber,
            'full_name' => $fullName,
            'gender' => $gender,
            'contact_number' => '0771001001',
            'guardian_name' => 'Parent of '.$fullName,
            'guardian_phone' => '0779998887',
        ]);
    }

    private function enrollStudent(ClassRoom $class, Student $student, string $uid): ClassStudent
    {
        return ClassStudent::create([
            'class_id' => $class->id,
            'student_id' => $student->id,
            'student_class_uid' => $uid,
            'qr_token' => 'qr-'.strtolower($uid),
            'status' => 'active',
        ]);
    }

    public function test_daily_sheet_displays_batch_wise_cash_book_and_present_absent_student_names(): void
    {
        $teacher = $this->createTeacher();
        $today = Carbon::today()->toDateString();

        $batchA = ClassRoom::create([
            'teacher_id' => $teacher->id,
            'name' => '2026 A/L Physics Batch A',
            'grade_level' => 'Grade 13',
            'monthly_fee' => 3000,
        ]);

        $batchB = ClassRoom::create([
            'teacher_id' => $teacher->id,
            'name' => '2026 A/L Physics Batch B',
            'grade_level' => 'Grade 13',
            'monthly_fee' => 2500,
        ]);

        $presentStudent = $this->createStudent('ADM-1001', 'Kasun Bandara', 'male');
        $absentStudent = $this->createStudent('ADM-1002', 'Nethmi Silva', 'female');
        $batchBStudent = $this->createStudent('ADM-1003', 'Sahan Jayasuriya', 'male');

        $this->enrollStudent($batchA, $presentStudent, 'PHY-A-001');
        $this->enrollStudent($batchA, $absentStudent, 'PHY-A-002');
        $this->enrollStudent($batchB, $batchBStudent, 'PHY-B-001');

        Attendance::create([
            'class_id' => $batchA->id,
            'student_id' => $presentStudent->id,
            'date' => $today,
            'check_in_time' => '08:15:00',
            'status' => 'present',
            'marked_by' => $teacher->id,
        ]);

        Payment::create([
            'class_id' => $batchA->id,
            'student_id' => $presentStudent->id,
            'teacher_id' => $teacher->id,
            'collected_by' => $teacher->id,
            'payment_type' => 'monthly',
            'amount' => 3000,
            'payment_month' => Carbon::now()->format('Y-m'),
            'paid_date' => $today,
            'receipt_no' => 'REC-0001',
            'payment_method' => 'cash',
        ]);

        Payment::create([
            'class_id' => $batchB->id,
            'student_id' => $batchBStudent->id,
            'teacher_id' => $teacher->id,
            'collected_by' => $teacher->id,
            'payment_type' => 'daily',
            'amount' => 1500,
            'payment_month' => Carbon::now()->format('Y-m'),
            'paid_date' => $today,
            'receipt_no' => 'REC-0002',
            'payment_method' => 'bank_transfer',
        ]);

        $response = $this->actingAs($teacher)->get(route('teacher.daily.sheet', ['date' => $today]));

        $response->assertOk();
        // Batch-wise Cash Book totals & Grand Total (3000 + 1500 = 4500)
        $response->assertSee('Daily Cash Book — Batch Wise & Total', false);
        $response->assertSee('2026 A/L Physics Batch A');
        $response->assertSee('2026 A/L Physics Batch B');
        $response->assertSee('3,000.00');
        $response->assertSee('1,500.00');
        $response->assertSee('4,500.00');

        // Present & Absent student names in Attendance Report
        $response->assertSee('Attendance Report — Present & Absent Student Names', false);
        $response->assertSee('Kasun Bandara');
        $response->assertSee('Nethmi Silva');
        $response->assertSee('Sahan Jayasuriya');
        $response->assertViewHas('attendanceCount', 1);
        $response->assertViewHas('absentCount', 2);
    }

    public function test_teacher_can_create_paper_save_bulk_marks_and_view_graphs(): void
    {
        $teacher = $this->createTeacher();

        $class = ClassRoom::create([
            'teacher_id' => $teacher->id,
            'name' => 'O/L Mathematics',
            'grade_level' => 'Grade 11',
            'monthly_fee' => 2000,
        ]);

        $student1 = $this->createStudent('ADM-2001', 'Amaya Fernando', 'female');
        $student2 = $this->createStudent('ADM-2002', 'Dineth Perera', 'male');

        $this->enrollStudent($class, $student1, 'MATH-001');
        $this->enrollStudent($class, $student2, 'MATH-002');

        // 1. Create Paper
        $createResponse = $this->actingAs($teacher)->post(route('teacher.papers.store'), [
            'class_id' => $class->id,
            'title' => 'Paper 01 - Algebra',
            'paper_date' => '2026-11-15',
            'max_marks' => 100,
            'notes' => 'First term evaluation',
        ]);

        $paper = Paper::first();
        $this->assertNotNull($paper);
        $createResponse->assertRedirect(route('teacher.papers.show', $paper));

        // 2. Save Bulk Marks (Student 1 = 84, Student 2 = Absent)
        $saveResponse = $this->actingAs($teacher)->post(route('teacher.papers.save-marks', $paper), [
            'entries' => [
                [
                    'student_id' => $student1->id,
                    'marks' => 84,
                    'is_absent' => 0,
                    'remarks' => 'Excellent work',
                ],
                [
                    'student_id' => $student2->id,
                    'marks' => null,
                    'is_absent' => 1,
                    'remarks' => 'Medical leave',
                ],
            ],
        ]);

        $saveResponse->assertRedirect(route('teacher.papers.show', $paper));

        $this->assertDatabaseHas('paper_marks', [
            'paper_id' => $paper->id,
            'student_id' => $student1->id,
            'marks' => 84.00,
            'is_absent' => false,
            'remarks' => 'Excellent work',
        ]);

        $this->assertDatabaseHas('paper_marks', [
            'paper_id' => $paper->id,
            'student_id' => $student2->id,
            'marks' => null,
            'is_absent' => true,
        ]);

        // 3. View Paper Show Page (Marks + Bar/Doughnut Graph)
        $showResponse = $this->actingAs($teacher)->get(route('teacher.papers.show', $paper));
        $showResponse->assertOk();
        $showResponse->assertSee('Paper 01 - Algebra');
        $showResponse->assertSee('Student Paper Marks Graph');
        $showResponse->assertSee('Amaya Fernando');
        $showResponse->assertViewHas('avgPercent', 84.0);
        $showResponse->assertViewHas('gradeDistribution', [
            'A' => 1,
            'B' => 0,
            'C' => 0,
            'S' => 0,
            'W' => 0,
            'AB' => 1,
        ]);

        // 4. View Individual Student Progress Graph
        $graphResponse = $this->actingAs($teacher)->get(route('teacher.papers.student-graph', [$class, $student1]));
        $graphResponse->assertOk();
        $graphResponse->assertSee('Student Paper Marks Progress Graph (%)');
        $graphResponse->assertSee('Amaya Fernando');
        $graphResponse->assertSee('Paper 01 - Algebra');
        $graphResponse->assertViewHas('overallAverage', 84.0);
        $graphResponse->assertViewHas('bestScore', 84.0);
    }

    public function test_teacher_cannot_access_another_teachers_paper(): void
    {
        $teacher1 = $this->createTeacher('t1@test.lk');
        $teacher2 = $this->createTeacher('t2@test.lk');

        $class = ClassRoom::create([
            'teacher_id' => $teacher1->id,
            'name' => 'Chemistry Revision',
            'grade_level' => 'Grade 13',
            'monthly_fee' => 3000,
        ]);

        $paper = Paper::create([
            'class_id' => $class->id,
            'teacher_id' => $teacher1->id,
            'title' => 'Organic Paper 01',
            'paper_date' => '2026-11-10',
            'max_marks' => 100,
        ]);

        $this->actingAs($teacher2)
            ->get(route('teacher.papers.show', $paper))
            ->assertForbidden();

        $this->actingAs($teacher2)
            ->delete(route('teacher.papers.destroy', $paper))
            ->assertForbidden();
    }

    public function test_teacher_can_delete_own_class_but_assistant_and_other_teachers_cannot(): void
    {
        $teacher1 = $this->createTeacher('owner@test.lk');
        $teacher2 = $this->createTeacher('other@test.lk');

        $assistant = User::create([
            'name' => 'Class Assistant',
            'email' => 'assistant@test.lk',
            'password' => Hash::make('password123'),
            'role' => 'assistant',
            'teacher_id' => $teacher1->id,
            'status' => 'active',
        ]);

        $class = ClassRoom::create([
            'teacher_id' => $teacher1->id,
            'name' => 'Biology Evening Batch',
            'grade_level' => 'Grade 12',
            'monthly_fee' => 2500,
        ]);

        $assistant->assignedClasses()->attach($class->id);

        // Assistant cannot delete class
        $this->actingAs($assistant)
            ->delete(route('teacher.classes.destroy', $class))
            ->assertForbidden();

        // Another teacher cannot delete class
        $this->actingAs($teacher2)
            ->delete(route('teacher.classes.destroy', $class))
            ->assertForbidden();

        $this->assertDatabaseHas('classes', ['id' => $class->id]);

        // Owning teacher can delete class
        $this->actingAs($teacher1)
            ->delete(route('teacher.classes.destroy', $class))
            ->assertRedirect(route('teacher.classes.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('classes', ['id' => $class->id]);
    }
}
