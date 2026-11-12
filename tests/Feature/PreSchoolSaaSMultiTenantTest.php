<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\SchoolSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PreSchoolSaaSMultiTenantTest extends TestCase
{
    use RefreshDatabase;

    public function test_three_preschool_principals_can_register_and_create_isolated_tenants(): void
    {
        // 1. Register Global International Principal
        $resGlobal = $this->post(route('register.post'), [
            'name' => 'Mr. Perera',
            'email' => 'perera@global.test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'principal',
            'preschool_name' => 'Global International Pre-School',
            'contact_phone' => '0771110001',
        ]);
        $resGlobal->assertRedirect(route('login'));
        $this->assertDatabaseHas('school_settings', ['name' => 'Global International Pre-School']);

        // 2. Register Seven Bees Principal
        $resSeven = $this->post(route('register.post'), [
            'name' => 'Mrs. Silva',
            'email' => 'silva@sevenbees.test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'principal',
            'preschool_name' => 'Seven Bees Pre-School',
            'contact_phone' => '0772220002',
        ]);
        $resSeven->assertRedirect(route('login'));
        $this->assertDatabaseHas('school_settings', ['name' => 'Seven Bees Pre-School']);

        // 3. Register Miracle Principal
        $resMiracle = $this->post(route('register.post'), [
            'name' => 'Dr. Fernando',
            'email' => 'fernando@miracle.test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'principal',
            'preschool_name' => 'Miracle Pre-School',
            'contact_phone' => '0773330003',
        ]);
        $resMiracle->assertRedirect(route('login'));
        $this->assertDatabaseHas('school_settings', ['name' => 'Miracle Pre-School']);

        // Verify each has distinct tokens and slugs
        $schoolGlobal = SchoolSetting::where('name', 'Global International Pre-School')->first();
        $schoolSeven = SchoolSetting::where('name', 'Seven Bees Pre-School')->first();
        $schoolMiracle = SchoolSetting::where('name', 'Miracle Pre-School')->first();

        $this->assertNotEquals($schoolGlobal->registration_token, $schoolSeven->registration_token);
        $this->assertNotEquals($schoolSeven->registration_token, $schoolMiracle->registration_token);
        $this->assertNotEquals($schoolGlobal->slug, $schoolSeven->slug);
    }

    public function test_apply_online_tab_allows_principal_to_manage_link_and_portal(): void
    {
        $principal = User::create([
            'name' => 'Principal Seven',
            'email' => 'seven@preschool.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $school = SchoolSetting::create([
            'user_id' => $principal->id,
            'name' => 'Seven Bees Pre-School',
            'slug' => 'seven-bees',
            'registration_token' => 'seven-bees-token-12345',
            'academic_year' => '2026/2027',
            'is_admission_open' => true,
        ]);
        $principal->update(['school_setting_id' => $school->id]);

        // Principal accesses Apply Online dedicated management tab
        $response = $this->actingAs($principal)->get(route('admin.applications.link'));
        $response->assertStatus(200);
        $response->assertSee('Seven Bees Pre-School');
        $response->assertSee('/apply/seven-bees');
        $response->assertSee('seven-bees-token-12345');
        $response->assertSee('Share on WhatsApp');
        $response->assertSee('Entrance Noticeboard QR');

        // Principal can update portal slug and settings
        $updateRes = $this->actingAs($principal)->post(route('admin.applications.update-link-settings'), [
            'slug' => 'seven-bees-official',
            'academic_year' => '2027/2028',
            'is_admission_open' => 1,
        ]);
        $updateRes->assertSessionHas('success');
        $school->refresh();
        $this->assertEquals('seven-bees-official', $school->slug);
        $this->assertEquals('2027/2028', $school->academic_year);

        // Principal can regenerate link token
        $oldToken = $school->registration_token;
        $regenRes = $this->actingAs($principal)->post(route('admin.applications.regenerate-link'));
        $regenRes->assertSessionHas('success');
        $school->refresh();
        $this->assertNotEquals($oldToken, $school->registration_token);
    }

    public function test_common_application_form_with_dynamic_branding_and_strict_tenant_isolation(): void
    {
        // 1. Create Seven Bees Pre-School
        $principalSeven = User::create([
            'name' => 'Mrs. Seven',
            'email' => 'principal@sevenbees.test',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
            'status' => 'active',
        ]);
        $schoolSeven = SchoolSetting::create([
            'user_id' => $principalSeven->id,
            'name' => 'Seven Bees Pre-School',
            'slug' => 'seven-bees',
            'registration_token' => 'token-seven-bees-999',
            'academic_year' => '2026/2027',
            'is_admission_open' => true,
        ]);
        $principalSeven->update(['school_setting_id' => $schoolSeven->id]);

        // 2. Create Miracle Pre-School
        $principalMiracle = User::create([
            'name' => 'Dr. Miracle',
            'email' => 'principal@miracle.test',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
            'status' => 'active',
        ]);
        $schoolMiracle = SchoolSetting::create([
            'user_id' => $principalMiracle->id,
            'name' => 'Miracle Pre-School Academy',
            'slug' => 'miracle-academy',
            'registration_token' => 'token-miracle-888',
            'academic_year' => '2026/2027',
            'is_admission_open' => true,
        ]);
        $principalMiracle->update(['school_setting_id' => $schoolMiracle->id]);

        // 3. Parent 1 visits Seven Bees form via custom slug
        $formSeven = $this->get('/apply/seven-bees');
        $formSeven->assertStatus(200);
        $formSeven->assertSee('Seven Bees Pre-School');
        $formSeven->assertDontSee('Miracle Pre-School Academy');

        // Submit for Child Seven
        $this->post(route('public.apply.store', 'seven-bees'), [
            'child_full_name' => 'Senulya Seven',
            'date_of_birth' => '2023-03-12',
            'gender' => 'female',
            'target_grade_level' => 'Nursery',
            'whatsapp_number' => '0771000007',
            'home_address' => 'Colombo 07',
        ]);

        // 4. Parent 2 visits Miracle form via token
        $formMiracle = $this->get('/apply/token-miracle-888');
        $formMiracle->assertStatus(200);
        $formMiracle->assertSee('Miracle Pre-School Academy');
        $formMiracle->assertDontSee('Seven Bees Pre-School');

        // Submit for Child Miracle
        $this->post(route('public.apply.store', 'token-miracle-888'), [
            'child_full_name' => 'Mithun Miracle',
            'date_of_birth' => '2023-04-18',
            'gender' => 'male',
            'target_grade_level' => 'Playgroup',
            'whatsapp_number' => '0772000008',
            'home_address' => 'Kandy Road',
        ]);

        // 5. Verify Seven Bees Principal sees ONLY Child Seven
        $sevenReview = $this->actingAs($principalSeven)->get(route('admin.applications.index'));
        $sevenReview->assertStatus(200);
        $sevenReview->assertSee('Senulya Seven');
        $sevenReview->assertDontSee('Mithun Miracle');

        // 6. Verify Miracle Principal sees ONLY Child Miracle
        $miracleReview = $this->actingAs($principalMiracle)->get(route('admin.applications.index'));
        $miracleReview->assertStatus(200);
        $miracleReview->assertSee('Mithun Miracle');
        $miracleReview->assertDontSee('Senulya Seven');
    }

    public function test_classes_and_students_are_strictly_isolated_between_preschools(): void
    {
        // PreSchool 1: Seven Bees
        $principalSeven = User::create([
            'name' => 'Seven Admin',
            'email' => 'admin@seven.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active',
        ]);
        $schoolSeven = SchoolSetting::create([
            'user_id' => $principalSeven->id,
            'name' => 'Seven Bees Pre-School',
            'slug' => 'seven-bees-preschool',
            'registration_token' => 'token-seven-1',
        ]);
        $principalSeven->update(['school_setting_id' => $schoolSeven->id]);

        $classSeven = ClassRoom::create([
            'school_setting_id' => $schoolSeven->id,
            'teacher_id' => $principalSeven->id,
            'name' => 'Seven Bees Honeybees Playgroup',
            'grade_level' => 'Playgroup',
            'fee_type' => 'monthly',
            'monthly_fee' => 4500,
        ]);

        // PreSchool 2: Miracle
        $principalMiracle = User::create([
            'name' => 'Miracle Admin',
            'email' => 'admin@miracle.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active',
        ]);
        $schoolMiracle = SchoolSetting::create([
            'user_id' => $principalMiracle->id,
            'name' => 'Miracle Pre-School',
            'slug' => 'miracle-preschool',
            'registration_token' => 'token-miracle-1',
        ]);
        $principalMiracle->update(['school_setting_id' => $schoolMiracle->id]);

        $classMiracle = ClassRoom::create([
            'school_setting_id' => $schoolMiracle->id,
            'teacher_id' => $principalMiracle->id,
            'name' => 'Miracle Stars Nursery',
            'grade_level' => 'Nursery',
            'fee_type' => 'monthly',
            'monthly_fee' => 5000,
        ]);

        // Miracle Principal dashboard only counts 1 class (Miracle Stars Nursery)
        $miracleDashboard = $this->actingAs($principalMiracle)->get(route('admin.dashboard'));
        $miracleDashboard->assertStatus(200);
        $this->assertEquals(1, $miracleDashboard->viewData('totalClasses'));

        // Seven Bees Principal dashboard only counts 1 class (Seven Bees Honeybees Playgroup)
        $sevenDashboard = $this->actingAs($principalSeven)->get(route('admin.dashboard'));
        $sevenDashboard->assertStatus(200);
        $this->assertEquals(1, $sevenDashboard->viewData('totalClasses'));
    }
}
