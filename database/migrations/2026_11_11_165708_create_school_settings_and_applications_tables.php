<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Pre-School Global/Tenant Settings (Name, Logo, Vision, Mission, Contact, Principal message)
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Little Stars International Pre-School');
            $table->string('motto')->nullable()->default('Nurturing Little Minds for a Brighter Tomorrow');
            $table->string('logo_path')->nullable();
            $table->text('vision')->nullable();
            $table->text('mission')->nullable();
            $table->text('about_us')->nullable();
            $table->text('principal_message')->nullable();
            $table->string('contact_phone', 40)->nullable()->default('+94 77 123 4567');
            $table->string('whatsapp_number', 40)->nullable()->default('+94 77 123 4567');
            $table->string('contact_email', 100)->nullable()->default('info@littlestars.lk');
            $table->text('address')->nullable()->default('123 Temple Road, Colombo, Sri Lanka');
            $table->string('academic_year', 20)->default('2026/2027');
            $table->boolean('is_admission_open')->default(true);
            $table->timestamps();
        });

        // 2. Add extra child-specific fields to students table
        Schema::table('students', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->after('full_name');
            $table->string('blood_group', 10)->nullable()->after('gender');
            $table->text('allergies_medical_notes')->nullable()->after('blood_group');
            $table->string('emergency_contact_name')->nullable()->after('guardian_phone');
            $table->string('emergency_contact_phone', 40)->nullable()->after('emergency_contact_name');
            $table->text('authorized_pickup_persons')->nullable()->after('emergency_contact_phone');
            $table->text('home_address')->nullable()->after('authorized_pickup_persons');
        });

        // 3. Online Admission Applications Table (from WhatsApp/online link)
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_number', 50)->unique();
            $table->string('child_full_name');
            $table->string('child_calling_name')->nullable();
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female']);
            $table->string('blood_group', 10)->nullable();
            $table->string('photo_path')->nullable();
            $table->string('target_grade_level', 50)->default('Nursery'); // Nursery, Playgroup, LKG, UKG, Daycare

            // Parent/Guardian Info
            $table->string('father_name')->nullable();
            $table->string('father_phone', 40)->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('mother_phone', 40)->nullable();
            $table->string('mother_occupation')->nullable();
            $table->string('whatsapp_number', 40);
            $table->string('email', 120)->nullable();
            $table->text('home_address');
            $table->text('medical_notes')->nullable();
            $table->text('authorized_pickup_persons')->nullable();

            // Application Status & Approval
            $table->enum('status', ['pending', 'interview_scheduled', 'approved', 'rejected', 'enrolled'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            // If approved & enrolled
            $table->foreignId('assigned_class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('created_student_id')->nullable()->constrained('students')->nullOnDelete();

            $table->timestamps();
        });

        // 4. Update payments table to support multi-category fees
        Schema::table('payments', function (Blueprint $table) {
            $table->string('category', 60)->default('monthly_tuition')->after('payment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('category');
        });

        Schema::dropIfExists('applications');

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'date_of_birth',
                'blood_group',
                'allergies_medical_notes',
                'emergency_contact_name',
                'emergency_contact_phone',
                'authorized_pickup_persons',
                'home_address',
            ]);
        });

        Schema::dropIfExists('school_settings');
    }
};
