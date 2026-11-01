<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeacherRegistrationAndApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

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
    }

    public function test_teacher_registration_screen_can_be_rendered(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('Create Teacher Account');
        $response->assertSee('Register Teacher Account');
    }

    public function test_user_can_register_teacher_account_with_pending_status(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => 'Dr. Marie Curie',
            'email' => 'marie.curie@edutrack.test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $user = User::where('email', 'marie.curie@edutrack.test')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Dr. Marie Curie', $user->name);
        $this->assertEquals('teacher', $user->role);
        $this->assertEquals('pending', $user->status);
        $this->assertTrue($user->isPending());
        $this->assertFalse($user->isActive());
    }

    public function test_registration_validation_fails_on_duplicate_email_or_mismatched_password(): void
    {
        User::create([
            'name' => 'Existing Teacher',
            'email' => 'existing@edutrack.test',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'status' => 'active',
        ]);

        $response = $this->post(route('register.post'), [
            'name' => 'Duplicate Email User',
            'email' => 'existing@edutrack.test',
            'password' => 'secret123',
            'password_confirmation' => 'mismatch456',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
    }

    public function test_pending_teacher_cannot_log_in_until_approved(): void
    {
        User::create([
            'name' => 'Pending Teacher',
            'email' => 'pending@edutrack.test',
            'password' => Hash::make('password123'),
            'role' => 'teacher',
            'status' => 'pending',
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'pending@edutrack.test',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_system_admin_can_view_pending_teachers_in_oversight(): void
    {
        $pendingTeacher = User::create([
            'name' => 'Applicant Teacher',
            'email' => 'applicant@edutrack.test',
            'password' => Hash::make('password123'),
            'role' => 'teacher',
            'status' => 'pending',
        ]);

        // Admin checks overview dashboard
        $dashboardResponse = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Pending Approval');

        // Admin views teachers page filtered by pending
        $teachersResponse = $this->actingAs($this->admin)->get(route('admin.teachers.index', ['status' => 'pending']));
        $teachersResponse->assertStatus(200);
        $teachersResponse->assertSee('Applicant Teacher');
        $teachersResponse->assertSee('Approve Account');
    }

    public function test_system_admin_can_approve_teacher_account(): void
    {
        $teacher = User::create([
            'name' => 'New Teacher',
            'email' => 'new@edutrack.test',
            'password' => Hash::make('password123'),
            'role' => 'teacher',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.users.approve', $teacher));

        $response->assertSessionHas('success');
        $this->assertEquals('active', $teacher->fresh()->status);
        $this->assertTrue($teacher->fresh()->isActive());
    }

    public function test_approved_teacher_can_log_in_and_access_dashboard(): void
    {
        $teacher = User::create([
            'name' => 'Approved Teacher',
            'email' => 'approved@edutrack.test',
            'password' => Hash::make('password123'),
            'role' => 'teacher',
            'status' => 'active',
        ]);

        $loginResponse = $this->post(route('login.post'), [
            'email' => 'approved@edutrack.test',
            'password' => 'password123',
        ]);

        $loginResponse->assertRedirect(route('teacher.dashboard'));
        $this->assertAuthenticatedAs($teacher);

        $dashboardResponse = $this->actingAs($teacher)->get(route('teacher.dashboard'));
        $dashboardResponse->assertStatus(200);
    }

    public function test_system_admin_can_block_and_deactivate_teacher_accounts(): void
    {
        $teacher = User::create([
            'name' => 'Active Teacher',
            'email' => 'activeteacher@edutrack.test',
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
