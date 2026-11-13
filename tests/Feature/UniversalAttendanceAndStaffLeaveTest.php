<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\ClassStudent;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\TeacherAttendance;
use App\Models\TeacherLeave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniversalAttendanceAndStaffLeaveTest extends TestCase
{
    use RefreshDatabase;

    protected User $principal;

    protected User $teacher;

    protected SchoolSetting $school;

    protected ClassRoom $classRoom;

    protected Student $student;

    protected ClassStudent $enrollment;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create School & Principal
        $this->school = SchoolSetting::create([
            'name' => 'Global International Pre-School',
            'is_admission_open' => true,
        ]);

        $this->principal = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'school_setting_id' => $this->school->id,
            'qr_token' => 'PRN-99999',
        ]);

        // 2. Create Teacher
        $this->teacher = User::factory()->create([
            'role' => 'teacher',
            'status' => 'active',
            'school_setting_id' => $this->school->id,
            'teacher_id' => $this->principal->id,
            'qr_token' => 'TCH-12345',
        ]);

        // 3. Create Class (e.g. LKG)
        $this->classRoom = ClassRoom::create([
            'school_setting_id' => $this->school->id,
            'teacher_id' => $this->teacher->id,
            'name' => 'LKG - Rose Buds',
            'grade_level' => 'LKG',
            'monthly_fee' => 3500.00,
            'fee_type' => 'monthly',
            'is_registration_open' => true,
        ]);

        // 4. Create Student & Enrollment
        $this->student = Student::create([
            'full_name' => 'Kasun Perera',
            'admission_number' => 'ADM-2026-001',
            'contact_number' => '0771234567',
            'guardian_name' => 'Nimal Perera',
            'guardian_phone' => '0771234567',
        ]);

        $this->enrollment = ClassStudent::create([
            'class_id' => $this->classRoom->id,
            'student_id' => $this->student->id,
            'student_class_uid' => 'CLS-LKG-001',
            'qr_token' => 'STD-TOKEN-1234567890',
            'status' => 'active',
        ]);
    }

    public function test_apply_page_redirects_or_shows_selector(): void
    {
        $this->school->refresh();

        // When only 1 school exists: redirects to that school's form
        $response = $this->get('/apply');
        $response->assertRedirect(route('public.apply', $this->school->slug ?: $this->school->registration_token));

        // When second school exists: shows selector
        SchoolSetting::create([
            'name' => 'Miracle Kids Academy',
            'is_admission_open' => true,
        ]);

        $responseMulti = $this->get('/apply');
        $responseMulti->assertStatus(200);
        $responseMulti->assertViewIs('public.select-school');
        $responseMulti->assertSee('Global International Pre-School');
        $responseMulti->assertSee('Miracle Kids Academy');
    }

    public function test_principal_can_view_classes_and_create_new_class(): void
    {
        $this->actingAs($this->principal);

        $response = $this->get(route('admin.classes.index'));
        $response->assertStatus(200);
        $response->assertSee('LKG - Rose Buds');

        $postResponse = $this->post(route('admin.classes.store'), [
            'name' => 'UKG - Sunflowers',
            'grade_level' => 'UKG',
            'monthly_fee' => 4000.00,
            'fee_type' => 'monthly',
            'teacher_id' => $this->teacher->id,
        ]);

        $postResponse->assertRedirect(route('admin.classes.index'));
        $this->assertDatabaseHas('classes', [
            'name' => 'UKG - Sunflowers',
            'grade_level' => 'UKG',
            'monthly_fee' => 4000.00,
            'school_setting_id' => $this->school->id,
        ]);
    }

    public function test_principal_can_update_and_delete_empty_class(): void
    {
        $this->actingAs($this->principal);

        $emptyClass = ClassRoom::create([
            'school_setting_id' => $this->school->id,
            'teacher_id' => $this->teacher->id,
            'name' => 'Playgroup Toddlers',
            'grade_level' => 'Playgroup',
            'monthly_fee' => 2500.00,
            'fee_type' => 'monthly',
        ]);

        $putResponse = $this->put(route('admin.classes.update', $emptyClass), [
            'name' => 'Playgroup Stars',
            'grade_level' => 'Playgroup',
            'monthly_fee' => 2800.00,
            'fee_type' => 'monthly',
            'teacher_id' => $this->teacher->id,
            'is_registration_open' => 1,
        ]);

        $putResponse->assertRedirect(route('admin.classes.index'));
        $this->assertDatabaseHas('classes', [
            'id' => $emptyClass->id,
            'name' => 'Playgroup Stars',
            'monthly_fee' => 2800.00,
        ]);

        // Delete empty class
        $delResponse = $this->delete(route('admin.classes.destroy', $emptyClass));
        $delResponse->assertRedirect(route('admin.classes.index'));
        $this->assertDatabaseMissing('classes', ['id' => $emptyClass->id]);
    }

    public function test_teacher_can_submit_leave_and_principal_can_approve(): void
    {
        // 1. Teacher applies for leave
        $this->actingAs($this->teacher);

        $indexRes = $this->get(route('teacher.leaves.index'));
        $indexRes->assertStatus(200);

        $applyRes = $this->post(route('teacher.leaves.store'), [
            'leave_type' => 'Medical',
            'start_date' => Carbon::today()->toDateString(),
            'end_date' => Carbon::tomorrow()->toDateString(),
            'reason' => 'Doctor appointment and medical checkup',
        ]);

        $applyRes->assertRedirect(route('teacher.leaves.index'));
        $this->assertDatabaseHas('teacher_leaves', [
            'teacher_id' => $this->teacher->id,
            'leave_type' => 'Medical',
            'status' => 'pending',
        ]);

        $leave = TeacherLeave::where('teacher_id', $this->teacher->id)->first();

        // 2. Principal reviews and approves leave
        $this->actingAs($this->principal);

        $adminRes = $this->get(route('admin.leaves.index'));
        $adminRes->assertStatus(200);
        $adminRes->assertSee('Doctor appointment and medical checkup');

        $approveRes = $this->post(route('admin.leaves.approve', $leave), [
            'admin_notes' => 'Get well soon',
        ]);

        $approveRes->assertStatus(302);
        $this->assertDatabaseHas('teacher_leaves', [
            'id' => $leave->id,
            'status' => 'approved',
            'admin_notes' => 'Get well soon',
        ]);
    }

    public function test_universal_scanner_identifies_and_marks_student_attendance(): void
    {
        $this->actingAs($this->principal);

        $scanPage = $this->get(route('universal.attendance.scan'));
        $scanPage->assertStatus(200);
        $scanPage->assertSee('Central Attendance Kiosk');

        // Post Student QR Token
        $response = $this->postJson(route('universal.attendance.scan.post'), [
            'qr_token' => $this->enrollment->qr_token,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'category' => 'student',
            'action' => 'checked_in',
            'name' => 'Kasun Perera',
            'class_name' => 'LKG - Rose Buds',
            'fee_status' => 'pending',
        ]);

        $this->assertDatabaseHas('attendances', [
            'class_id' => $this->classRoom->id,
            'student_id' => $this->student->id,
            'status' => 'present',
        ]);
    }

    public function test_universal_scanner_identifies_and_marks_teacher_attendance_checkin_and_checkout(): void
    {
        $this->actingAs($this->principal);

        // 1. First scan marks teacher Check-in
        $responseIn = $this->postJson(route('universal.attendance.scan.post'), [
            'qr_token' => $this->teacher->qr_token,
        ]);

        $responseIn->assertStatus(200);
        $responseIn->assertJson([
            'success' => true,
            'category' => 'teacher',
            'action' => 'check_in',
            'name' => $this->teacher->name,
        ]);

        $this->assertDatabaseHas('teacher_attendances', [
            'teacher_id' => $this->teacher->id,
            'status' => 'present',
        ]);

        // 2. Second scan marks teacher Check-out
        $responseOut = $this->postJson(route('universal.attendance.scan.post'), [
            'qr_token' => $this->teacher->qr_token,
        ]);

        $responseOut->assertStatus(200);
        $responseOut->assertJson([
            'success' => true,
            'category' => 'teacher',
            'action' => 'check_out',
        ]);

        $teacherAtt = TeacherAttendance::where('teacher_id', $this->teacher->id)->first();
        $this->assertNotNull($teacherAtt->check_out_time);
    }

    public function test_universal_scanner_returns_not_found_for_invalid_qr(): void
    {
        $this->actingAs($this->principal);

        $response = $this->postJson(route('universal.attendance.scan.post'), [
            'qr_token' => 'INVALID-TOKEN-99999',
        ]);

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'category' => 'unknown',
        ]);
    }

    public function test_teacher_id_pass_view(): void
    {
        $response = $this->get(route('public.teacher.pass', $this->teacher->qr_token));
        $response->assertStatus(200);
        $response->assertSee($this->teacher->name);
        $response->assertSee('Faculty Staff');
        $response->assertSee('Print ID Badge');
        $response->assertSee($this->teacher->qr_token);
    }
}
