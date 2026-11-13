<?php

namespace Tests\Feature;

use App\Models\SchoolSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminAndPrincipalApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed default Super Admin
        User::create([
            'name' => 'Super Administrator',
            'email' => 'admin@edutrack.test',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
    }

    public function test_principal_registration_creates_pending_account_and_preschool(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => 'Sister Mary',
            'email' => 'mary@sevenbees.lk',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'preschool_name' => 'Seven Bees Montessori Pre-School',
            'contact_phone' => '+94 77 987 6543',
            'account_type' => 'principal',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $principal = User::where('email', 'mary@sevenbees.lk')->first();
        $this->assertNotNull($principal);
        $this->assertEquals('pending', $principal->status);
        $this->assertTrue($principal->isPrincipal());

        $school = SchoolSetting::where('user_id', $principal->id)->first();
        $this->assertNotNull($school);
        $this->assertEquals('Seven Bees Montessori Pre-School', $school->name);
        $this->assertEquals($school->id, $principal->school_setting_id);
    }

    public function test_pending_principal_cannot_log_in_until_approved(): void
    {
        $school = SchoolSetting::create([
            'name' => 'Miracle Kids Pre-School',
            'registration_token' => 'miracle-token-123',
        ]);

        User::create([
            'name' => 'Principal David',
            'email' => 'david@miracle.lk',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
            'status' => 'pending',
            'school_setting_id' => $school->id,
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'david@miracle.lk',
            'password' => 'secret123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['email']);
    }

    public function test_super_admin_can_view_and_approve_pending_principal(): void
    {
        $superAdmin = User::where('email', 'admin@edutrack.test')->first();

        $principal = User::create([
            'name' => 'Principal Sarah',
            'email' => 'sarah@global.lk',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
            'status' => 'pending',
        ]);

        $school = SchoolSetting::create([
            'user_id' => $principal->id,
            'name' => 'Global International Pre-School',
            'registration_token' => 'global-token-456',
            'is_admission_open' => false,
        ]);
        $principal->update(['school_setting_id' => $school->id]);

        // Super Admin views pending principals
        $this->actingAs($superAdmin)
            ->get(route('super_admin.principals.index', ['status' => 'pending']))
            ->assertOk()
            ->assertSee('Principal Sarah')
            ->assertSee('Global International Pre-School');

        // Super Admin approves principal
        $approveResponse = $this->actingAs($superAdmin)
            ->post(route('super_admin.principals.approve', $principal));

        $approveResponse->assertSessionHas('success');

        $principal->refresh();
        $school->refresh();
        $this->assertEquals('active', $principal->status);
        $this->assertTrue($school->is_admission_open);

        // Now approved Principal can log in
        $loginResponse = $this->post(route('login.post'), [
            'email' => 'sarah@global.lk',
            'password' => 'secret123',
        ]);

        $this->assertAuthenticatedAs($principal);
        $loginResponse->assertRedirect(route('admin.dashboard'));
    }

    public function test_principal_can_manage_teachers_add_activate_and_block(): void
    {
        $school = SchoolSetting::create([
            'name' => 'Seven Bees Pre-School',
            'registration_token' => 'seven-token-789',
        ]);

        $principal = User::create([
            'name' => 'Principal Martha',
            'email' => 'martha@sevenbees.lk',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active',
            'school_setting_id' => $school->id,
        ]);

        // Principal adds teacher
        $createResponse = $this->actingAs($principal)
            ->post(route('admin.teachers.store'), [
                'name' => 'Teacher Jennifer',
                'email' => 'jennifer@sevenbees.lk',
                'password' => 'password123',
                'can_collect_fees' => '1',
                'can_manage_uniforms' => '1',
            ]);

        $createResponse->assertSessionHas('success');

        $teacher = User::where('email', 'jennifer@sevenbees.lk')->first();
        $this->assertNotNull($teacher);
        $this->assertEquals('active', $teacher->status);
        $this->assertEquals('teacher', $teacher->role);
        $this->assertEquals($school->id, $teacher->school_setting_id);
        $this->assertTrue($teacher->can_collect_fees);
        $this->assertTrue($teacher->can_manage_uniforms);

        // Principal blocks teacher
        $blockResponse = $this->actingAs($principal)
            ->post(route('admin.users.status', $teacher), [
                'status' => 'blocked',
            ]);

        $blockResponse->assertSessionHas('success');
        $teacher->refresh();
        $this->assertEquals('blocked', $teacher->status);

        // Blocked teacher cannot log in
        $this->post(route('logout'));
        $this->post(route('login.post'), [
            'email' => 'jennifer@sevenbees.lk',
            'password' => 'password123',
        ]);
        $this->assertGuest();

        // Principal unblocks / activates teacher
        $activateResponse = $this->actingAs($principal)
            ->post(route('admin.users.status', $teacher), [
                'status' => 'active',
            ]);

        $activateResponse->assertSessionHas('success');
        $teacher->refresh();
        $this->assertEquals('active', $teacher->status);

        // Teacher can now log in
        $this->post(route('login.post'), [
            'email' => 'jennifer@sevenbees.lk',
            'password' => 'password123',
        ]);
        $this->assertAuthenticatedAs($teacher);
    }

    public function test_super_admin_can_block_and_activate_principal(): void
    {
        $superAdmin = User::where('email', 'admin@edutrack.test')->first();

        $principal = User::create([
            'name' => 'Principal Helen',
            'email' => 'helen@test.lk',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Super Admin blocks principal
        $this->actingAs($superAdmin)
            ->post(route('super_admin.principals.block', $principal))
            ->assertSessionHas('info');

        $principal->refresh();
        $this->assertEquals('blocked', $principal->status);

        // Super Admin activates principal
        $this->actingAs($superAdmin)
            ->post(route('super_admin.principals.activate', $principal))
            ->assertSessionHas('success');

        $principal->refresh();
        $this->assertEquals('active', $principal->status);
    }
}
