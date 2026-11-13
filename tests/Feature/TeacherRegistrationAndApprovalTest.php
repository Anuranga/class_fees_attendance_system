<?php

namespace Tests\Feature;

use App\Models\SchoolSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeacherRegistrationAndApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected SchoolSetting $school;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = SchoolSetting::create([
            'name' => 'Royal Pre-School Academy',
            'registration_token' => 'royal-token-123',
        ]);

        $this->admin = User::create([
            'name' => 'Principal Perera',
            'email' => 'principal@royal.lk',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
            'school_setting_id' => $this->school->id,
        ]);

        $this->school->update(['user_id' => $this->admin->id]);
    }

    public function test_principal_registration_screen_can_be_rendered(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('Register Your Pre-School');
        $response->assertSee('Principal Full Name');
        $response->assertSee('Pre-School / Nursery Name');
        $response->assertSee('Teacher accounts and individual permissions are created directly by your Pre-School\'s Principal', false);
    }

    public function test_principal_registration_requires_preschool_name(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => 'Sister Anne',
            'email' => 'anne@school.lk',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            // Missing preschool_name
        ]);

        $response->assertSessionHasErrors(['preschool_name']);
        $this->assertDatabaseMissing('users', ['email' => 'anne@school.lk']);
    }

    public function test_principal_registers_preschool_and_pending_account(): void
    {
        $response = $this->post(route('register.post'), [
            'preschool_name' => 'Happy Valley Kindergarten',
            'name' => 'Sister Anne',
            'email' => 'anne@happyvalley.lk',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'contact_phone' => '077 111 2233',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $user = User::where('email', 'anne@happyvalley.lk')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Sister Anne', $user->name);
        $this->assertEquals('admin', $user->role);
        $this->assertEquals('pending', $user->status);
        $this->assertTrue($user->isPrincipal());

        $school = SchoolSetting::where('user_id', $user->id)->first();
        $this->assertNotNull($school);
        $this->assertEquals('Happy Valley Kindergarten', $school->name);
    }

    public function test_principal_creates_teacher_directly_for_their_preschool(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.teachers.store'), [
            'name' => 'Teacher Kumari',
            'email' => 'kumari@royal.lk',
            'password' => 'secret123',
            'can_collect_fees' => '1',
            'can_manage_uniforms' => '1',
        ]);

        $response->assertSessionHas('success');

        $teacher = User::where('email', 'kumari@royal.lk')->first();
        $this->assertNotNull($teacher);
        $this->assertEquals('Teacher Kumari', $teacher->name);
        $this->assertEquals('teacher', $teacher->role);
        $this->assertEquals('active', $teacher->status);
        $this->assertEquals($this->school->id, $teacher->school_setting_id);
        $this->assertEquals($this->admin->id, $teacher->teacher_id);
        $this->assertTrue($teacher->can_collect_fees);
        $this->assertTrue($teacher->can_manage_uniforms);
    }

    public function test_principal_can_toggle_teacher_operational_permissions(): void
    {
        $teacher = User::create([
            'school_setting_id' => $this->school->id,
            'teacher_id' => $this->admin->id,
            'name' => 'Teacher Fernando',
            'email' => 'fernando@royal.lk',
            'password' => Hash::make('password123'),
            'role' => 'teacher',
            'status' => 'active',
            'can_collect_fees' => true,
            'can_manage_uniforms' => false,
        ]);

        // Revoke fees permission and grant uniform permission
        $response = $this->actingAs($this->admin)->post(route('admin.teachers.permissions', $teacher), [
            'can_collect_fees' => '0',
            'can_manage_uniforms' => '1',
        ]);

        $response->assertSessionHas('success');

        $teacher->refresh();
        $this->assertFalse($teacher->can_collect_fees);
        $this->assertTrue($teacher->can_manage_uniforms);
    }

    public function test_principal_cannot_modify_teachers_of_another_preschool(): void
    {
        $otherSchool = SchoolSetting::create([
            'name' => 'Other Pre-School',
            'registration_token' => 'other-token-999',
        ]);

        $otherTeacher = User::create([
            'school_setting_id' => $otherSchool->id,
            'name' => 'Teacher Outside',
            'email' => 'outside@other.lk',
            'password' => Hash::make('password123'),
            'role' => 'teacher',
            'status' => 'active',
            'can_collect_fees' => true,
            'can_manage_uniforms' => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.teachers.permissions', $otherTeacher), [
            'can_collect_fees' => '0',
            'can_manage_uniforms' => '0',
        ]);

        $response->assertSessionHas('error');
        $otherTeacher->refresh();
        $this->assertTrue($otherTeacher->can_collect_fees); // Unchanged!
    }

    public function test_pending_teacher_cannot_log_in_until_approved(): void
    {
        User::create([
            'school_setting_id' => $this->school->id,
            'name' => 'Pending Teacher',
            'email' => 'pending@royal.lk',
            'password' => Hash::make('password123'),
            'role' => 'teacher',
            'status' => 'pending',
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'pending@royal.lk',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_principal_can_block_and_deactivate_teacher_accounts(): void
    {
        $teacher = User::create([
            'school_setting_id' => $this->school->id,
            'teacher_id' => $this->admin->id,
            'name' => 'Active Teacher',
            'email' => 'activeteacher@royal.lk',
            'password' => Hash::make('password123'),
            'role' => 'teacher',
            'status' => 'active',
        ]);

        // 1. Block account
        $blockResponse = $this->actingAs($this->admin)->post(route('admin.users.status', $teacher), [
            'status' => 'blocked',
        ]);
        $blockResponse->assertSessionHas('success');
        $this->assertEquals('blocked', $teacher->fresh()->status);
        $this->assertTrue($teacher->fresh()->isBlocked());

        // Blocked teacher cannot access teacher dashboard
        $accessResponse = $this->actingAs($teacher->fresh())->get(route('teacher.dashboard'));
        $accessResponse->assertRedirect(route('login'));
        $accessResponse->assertSessionHas('error');

        // 2. Set to inactive
        $inactiveResponse = $this->actingAs($this->admin)->post(route('admin.users.status', $teacher), [
            'status' => 'inactive',
        ]);
        $inactiveResponse->assertSessionHas('success');
        $this->assertEquals('inactive', $teacher->fresh()->status);
        $this->assertTrue($teacher->fresh()->isInactive());

        // 3. Reactivate account
        $reactivateResponse = $this->actingAs($this->admin)->post(route('admin.users.status', $teacher), [
            'status' => 'active',
        ]);
        $reactivateResponse->assertSessionHas('success');
        $this->assertEquals('active', $teacher->fresh()->status);
        $this->assertTrue($teacher->fresh()->isActive());
    }
}
