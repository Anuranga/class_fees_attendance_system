<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\ClassStudent;
use App\Models\Student;
use App\Models\UniformOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PrincipalAndUniformWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $principal;

    protected User $teacher;

    protected User $assignedTeacher;

    protected ClassRoom $classRoom;

    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Principal (Admin)
        $this->principal = User::create([
            'name' => 'Principal Perera',
            'email' => 'principal@preschool.lk',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
            'can_collect_fees' => true,
            'can_manage_uniforms' => true,
        ]);

        // 2. Create Regular Teacher (Attendance only by default)
        $this->teacher = User::create([
            'name' => 'Teacher Nimali',
            'email' => 'nimali@preschool.lk',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'status' => 'active',
            'can_collect_fees' => false,
            'can_manage_uniforms' => false,
        ]);

        // 3. Create Teacher specifically assigned for fee & uniform collection
        $this->assignedTeacher = User::create([
            'name' => 'Teacher Kasun (Cashier)',
            'email' => 'kasun@preschool.lk',
            'password' => Hash::make('password'),
            'role' => 'assistant',
            'status' => 'active',
            'teacher_id' => $this->principal->id,
            'can_collect_fees' => true,
            'can_manage_uniforms' => true,
        ]);

        $this->classRoom = ClassRoom::create([
            'teacher_id' => $this->principal->id,
            'name' => 'Nursery Sunflowers',
            'grade_level' => 'Nursery',
            'monthly_fee' => 3500.00,
            'registration_token' => 'nursery-sunflowers',
            'is_registration_open' => true,
        ]);

        $this->assignedTeacher->assignedClasses()->attach($this->classRoom->id);

        $this->student = Student::create([
            'admission_number' => 'ADM-PS-100',
            'full_name' => 'Senulya De Silva',
            'gender' => 'female',
            'guardian_name' => 'Kamal De Silva',
            'guardian_phone' => '0771234567',
            'contact_number' => '0771234567',
        ]);

        ClassStudent::create([
            'class_id' => $this->classRoom->id,
            'student_id' => $this->student->id,
            'student_class_uid' => 'CLS-1-STD-100',
            'qr_token' => 'qr-token-senulya',
            'joined_via' => 'manual',
            'status' => 'active',
        ]);
    }

    public function test_user_can_register_as_principal_or_teacher(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => 'New Principal User',
            'email' => 'newprincipal@preschool.lk',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'principal',
        ]);

        $response->assertRedirect(route('login'));
        $user = User::where('email', 'newprincipal@preschool.lk')->first();
        $this->assertNotNull($user);
        $this->assertEquals('admin', $user->role);
    }

    public function test_assigned_teacher_can_record_uniform_order_and_mark_paid_with_sinhala_message(): void
    {
        // Assigned teacher records an order for uniform
        $response = $this->actingAs($this->assignedTeacher)->post(route('teacher.uniforms.store'), [
            'student_id' => $this->student->id,
            'item_name' => 'Pre-School Uniform Set with Hat',
            'size' => 'Size 26',
            'quantity' => 2,
            'unit_price' => 2200.00,
            'mark_as_paid' => '1',
        ]);

        $response->assertSessionHas('success');

        $order = UniformOrder::where('student_id', $this->student->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals(4400.00, $order->total_amount);
        $this->assertEquals('paid', $order->payment_status);
        $this->assertNotNull($order->receipt_no);

        // Verify Sinhala WhatsApp message contains required keys and student name
        $sinhalaMsg = $order->getSinhalaPaymentMessage('Little Stars Pre-School');
        $this->assertStringContainsString('Senulya De Silva', $sinhalaMsg);
        $this->assertStringContainsString('නිල ඇඳුම් මුදල', $sinhalaMsg);
        $this->assertStringContainsString('ලදුපත් අංකය', $sinhalaMsg);

        // Teacher marks uniform as delivered
        $deliverResponse = $this->actingAs($this->assignedTeacher)->post(route('teacher.uniforms.deliver', $order));
        $deliverResponse->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('delivered', $order->delivery_status);

        $deliverMsg = $order->getSinhalaDeliveryMessage('Little Stars Pre-School');
        $this->assertStringContainsString('නිල ඇඳුම් භාරදීමේ දැනුම්දීම', $deliverMsg);
        $this->assertStringContainsString('Senulya De Silva', $deliverMsg);
    }

    public function test_unauthorized_teacher_without_permission_cannot_access_uniforms(): void
    {
        $response = $this->actingAs($this->teacher)->get(route('teacher.uniforms.index'));
        $response->assertStatus(403);
    }
}
