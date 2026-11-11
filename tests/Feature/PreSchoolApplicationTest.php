<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ClassRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PreSchoolApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $teacher;

    protected ClassRoom $classRoom;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Principal',
            'email' => 'principal@littlestars.lk',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->teacher = User::create([
            'name' => 'Teacher Mary',
            'email' => 'mary@littlestars.lk',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'status' => 'active',
        ]);

        $this->classRoom = ClassRoom::create([
            'teacher_id' => $this->teacher->id,
            'name' => 'Nursery Butterflies 2026',
            'grade_level' => 'Nursery',
            'monthly_fee' => 5000.00,
            'registration_token' => 'nursery-2026-token',
            'is_registration_open' => true,
        ]);
    }

    public function test_parent_can_submit_online_pre_school_admission_application(): void
    {
        $postData = [
            'child_full_name' => 'Liam Chen Fernando',
            'child_calling_name' => 'Liam',
            'date_of_birth' => '2023-05-15',
            'gender' => 'male',
            'blood_group' => 'O+',
            'target_grade_level' => 'Nursery',
            'father_name' => 'David Fernando',
            'father_phone' => '0771112222',
            'mother_name' => 'Sarah Fernando',
            'mother_phone' => '0773334444',
            'whatsapp_number' => '0779998888',
            'home_address' => '45 Flower Road, Colombo 07',
            'authorized_pickup_persons' => 'Grandmother (Mrs. Silva)',
            'medical_notes' => 'No allergies known',
        ];

        $response = $this->post(route('public.apply.store'), $postData);

        $this->assertDatabaseHas('applications', [
            'child_full_name' => 'Liam Chen Fernando',
            'whatsapp_number' => '0779998888',
            'status' => 'pending',
        ]);

        $app = Application::where('whatsapp_number', '0779998888')->first();
        $this->assertNotNull($app);
        $this->assertStringStartsWith('APP-', $app->application_number);

        $response->assertRedirect(route('public.apply.success', $app->application_number));

        // Test status check
        $statusResponse = $this->get(route('public.apply.status', ['query' => $app->application_number]));
        $statusResponse->assertStatus(200);
        $statusResponse->assertSee('Liam Chen Fernando');
        $statusResponse->assertSee('Under Review by Principal');
    }

    public function test_principal_can_approve_and_enroll_applicant_generating_student_and_qr_pass(): void
    {
        $app = Application::create([
            'application_number' => 'APP-2026-0099',
            'child_full_name' => 'Maya Senulya',
            'date_of_birth' => '2023-08-20',
            'gender' => 'female',
            'target_grade_level' => 'Nursery',
            'whatsapp_number' => '0771239999',
            'home_address' => 'Colombo',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.applications.enroll', $app), [
            'class_id' => $this->classRoom->id,
            'admission_number' => 'ADM-PS-001',
        ]);

        $response->assertSessionHas('success');

        $app->refresh();
        $this->assertEquals('enrolled', $app->status);
        $this->assertNotNull($app->created_student_id);

        $student = $app->student;
        $this->assertNotNull($student);
        $this->assertEquals('ADM-PS-001', $student->admission_number);
        $this->assertEquals('Maya Senulya', $student->full_name);

        // Verify class enrollment & QR token generated
        $enrollment = $student->enrollments->where('class_id', $this->classRoom->id)->first();
        $this->assertNotNull($enrollment);
        $this->assertNotNull($enrollment->qr_token);

        // Verify QR pass view loads
        $passResponse = $this->get(route('public.pass', $enrollment->qr_token));
        $passResponse->assertStatus(200);
        $passResponse->assertSee('Maya Senulya');
    }
}
