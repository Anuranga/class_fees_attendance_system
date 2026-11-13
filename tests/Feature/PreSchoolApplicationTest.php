<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ClassRoom;
use App\Models\SchoolSetting;
use App\Models\Student;
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

    public function test_enrolling_application_with_duplicate_admission_number_fails_validation(): void
    {
        // Existing student with admission number '001'
        Student::create([
            'admission_number' => '001',
            'full_name' => 'Existing Student',
            'contact_number' => '0771112233',
            'guardian_name' => 'Guardian',
            'guardian_phone' => '0771112233',
        ]);

        $app = Application::create([
            'school_setting_id' => SchoolSetting::get()->id,
            'application_number' => 'APP-2026-9999',
            'child_full_name' => 'Duplicate Candidate',
            'date_of_birth' => '2022-01-01',
            'gender' => 'male',
            'target_grade_level' => 'Nursery',
            'whatsapp_number' => '0779998888',
            'home_address' => 'Colombo Road',
            'status' => 'pending',
        ]);

        // Try to enroll with already taken '001'
        $response = $this->actingAs($this->admin)->post(route('admin.applications.enroll', $app), [
            'class_id' => $this->classRoom->id,
            'admission_number' => '001',
        ]);

        $response->assertSessionHasErrors(['admission_number']);
        $this->assertDatabaseMissing('students', ['full_name' => 'Duplicate Candidate']);
    }

    public function test_parent_can_access_application_form_in_sinhala_via_tokenized_whatsapp_link(): void
    {
        $school = SchoolSetting::get();
        $this->assertNotNull($school->registration_token);

        // Access via tokenized WhatsApp share link
        $response = $this->get(route('public.apply', ['token' => $school->registration_token]));
        $response->assertStatus(200);
        $response->assertSee('පෙරපාසල් ළමා ඇතුළත් කිරීම් අයදුම්පත');
        $response->assertSee('දරුවාගේ සම්පූර්ණ නම');
        $response->assertSee('WhatsApp අංකය');
        $response->assertSee($school->name);

        // Submit via tokenized WhatsApp link
        $postData = [
            'child_full_name' => 'Kavindu Perera',
            'date_of_birth' => '2023-01-15',
            'gender' => 'male',
            'target_grade_level' => 'Playgroup',
            'whatsapp_number' => '0775551234',
            'home_address' => 'Galle Road, Moratuwa',
        ];

        $postResponse = $this->post(route('public.apply.store', ['token' => $school->registration_token]), $postData);
        $this->assertDatabaseHas('applications', [
            'child_full_name' => 'Kavindu Perera',
            'school_setting_id' => $school->id,
            'whatsapp_number' => '0775551234',
        ]);

        $app = Application::where('whatsapp_number', '0775551234')->first();
        $postResponse->assertRedirect(route('public.apply.success', $app->application_number));
    }

    public function test_application_generates_descriptive_sinhala_selection_message_with_items_checklist(): void
    {
        $school = SchoolSetting::get();
        $app = Application::create([
            'school_setting_id' => $school->id,
            'application_number' => 'APP-2026-0777',
            'child_full_name' => 'Senali Amanda',
            'date_of_birth' => '2023-05-10',
            'gender' => 'female',
            'target_grade_level' => 'Nursery',
            'whatsapp_number' => '0771122334',
            'home_address' => 'Nugegoda',
            'status' => 'approved',
        ]);

        $sinhalaMsg = $app->getSinhalaSelectionMessage();

        $this->assertStringContainsString('සුභ පැතුම්!', $sinhalaMsg);
        $this->assertStringContainsString('Senali Amanda', $sinhalaMsg);
        $this->assertStringContainsString($school->name, $sinhalaMsg);
        $this->assertStringContainsString('පළමු දිනයේ රැගෙන ආ යුතු අත්‍යවශ්‍ය ද්‍රව්‍ය ලේඛනය', $sinhalaMsg);
        $this->assertStringContainsString('උප්පැන්න සහතිකයේ', $sinhalaMsg);
        $this->assertStringContainsString($app->application_number, $sinhalaMsg);

        // Review page displays WhatsApp button and Sinhala message preview
        $showResponse = $this->actingAs($this->admin)->get(route('admin.applications.show', $app));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Send WhatsApp Selection Message');
        $showResponse->assertSee('සුභ පැතුම්!');
    }

    public function test_principal_can_customize_day_one_checklist_and_view_sharable_link(): void
    {
        $school = SchoolSetting::get();

        $response = $this->actingAs($this->admin)->put(route('admin.settings.school.update'), [
            'name' => 'Little Blossoms Pre-School Academy',
            'motto' => 'Love and Joy',
            'academic_year' => '2026/2027',
            'is_admission_open' => 1,
            'items_to_bring_sinhala' => "1. උප්පැන්න සහතිකය\n2. පානීය ජල බෝතලය\n3. හුණු කූරු සහ පැන්සල් පෙට්ටිය",
        ]);

        $response->assertSessionHas('success');

        $school->refresh();
        $this->assertEquals('Little Blossoms Pre-School Academy', $school->name);
        $this->assertStringContainsString('හුණු කූරු සහ පැන්සල් පෙට්ටිය', $school->items_to_bring_sinhala);

        // Settings page shows WhatsApp sharable admission link
        $settingsView = $this->actingAs($this->admin)->get(route('admin.settings.school'));
        $settingsView->assertStatus(200);
        $settingsView->assertSee($school->registration_url);
        $settingsView->assertSee('Share on WhatsApp');
    }
}
