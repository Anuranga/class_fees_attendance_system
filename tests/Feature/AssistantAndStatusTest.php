<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\ClassStudent;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AssistantAndStatusTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $teacher;

    protected ClassRoom $classRoom;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@edutrack.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->teacher = User::create([
            'name' => 'Prof. Alan Turing',
            'email' => 'teacher@edutrack.test',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'status' => 'active',
        ]);

        $this->classRoom = ClassRoom::create([
            'teacher_id' => $this->teacher->id,
            'name' => 'Advanced Math',
            'grade_level' => 'Grade 12',
            'monthly_fee' => 3000.00,
            'registration_token' => 'math12-token',
            'is_registration_open' => true,
        ]);
    }

    public function test_teacher_can_create_and_toggle_assistant(): void
    {
        // 1. Teacher creates an assistant
        $response = $this->actingAs($this->teacher)->post(route('teacher.assistants.store'), [
            'name' => 'Kasun Perera',
            'email' => 'kasun@edutrack.test',
            'password' => 'password123',
        ]);

        $response->assertSessionHas('success');

        $assistant = User::where('email', 'kasun@edutrack.test')->first();
        $this->assertNotNull($assistant);
        $this->assertEquals('assistant', $assistant->role);
        $this->assertEquals($this->teacher->id, $assistant->teacher_id);
        $this->assertEquals('active', $assistant->status);

        // 2. Teacher toggles assistant to inactive
        $this->actingAs($this->teacher)->post(route('teacher.assistants.toggle-status', $assistant));
        $this->assertEquals('inactive', $assistant->fresh()->status);

        // 3. Teacher toggles assistant back to active
        $this->actingAs($this->teacher)->post(route('teacher.assistants.toggle-status', $assistant));
        $this->assertEquals('active', $assistant->fresh()->status);
    }

    public function test_assistant_can_mark_attendance_with_tute_provided(): void
    {
        $assistant = User::create([
            'name' => 'Assistant Dave',
            'email' => 'dave@edutrack.test',
            'password' => Hash::make('password'),
            'role' => 'assistant',
            'status' => 'active',
            'teacher_id' => $this->teacher->id,
        ]);

        $student = Student::create([
            'admission_number' => 'ADM-101',
            'full_name' => 'Nimal Silva',
            'contact_number' => '0771234567',
            'guardian_name' => 'Sunil Silva',
            'guardian_phone' => '0777654321',
        ]);

        $enrollment = ClassStudent::create([
            'class_id' => $this->classRoom->id,
            'student_id' => $student->id,
            'student_class_uid' => 'CLS-1-STD-101',
            'qr_token' => 'qr-token-nimal',
            'joined_via' => 'manual',
            'status' => 'active',
        ]);

        // Assistant logs in and scans QR code with tute_provided = true
        $scanResponse = $this->actingAs($assistant)->postJson(route('teacher.attendance.scan', $this->classRoom), [
            'qr_token' => $enrollment->qr_token,
            'tute_provided' => true,
        ]);

        $scanResponse->assertStatus(200);
        $scanResponse->assertJson([
            'success' => true,
            'status' => 'marked',
        ]);

        $record = Attendance::where('class_id', $this->classRoom->id)
            ->where('student_id', $student->id)
            ->first();

        $this->assertNotNull($record);
        $this->assertTrue($record->tute_provided);
        $this->assertEquals($assistant->id, $record->marked_by);
    }

    public function test_inactive_or_blocked_user_cannot_access_workspace(): void
    {
        $blockedTeacher = User::create([
            'name' => 'Blocked Teacher',
            'email' => 'blocked@edutrack.test',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'status' => 'blocked',
        ]);

        $response = $this->actingAs($blockedTeacher)->get(route('teacher.dashboard'));
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
    }

    public function test_admin_can_update_user_status(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.status', $this->teacher), [
            'status' => 'blocked',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('blocked', $this->teacher->fresh()->status);

        // Admin unblocks
        $this->actingAs($this->admin)->post(route('admin.users.status', $this->teacher), [
            'status' => 'active',
        ]);
        $this->assertEquals('active', $this->teacher->fresh()->status);
    }
}
