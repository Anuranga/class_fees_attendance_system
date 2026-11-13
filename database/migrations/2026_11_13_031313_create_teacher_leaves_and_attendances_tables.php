<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add qr_token to users table for Teacher QR Passes
        Schema::table('users', function (Blueprint $table) {
            $table->string('qr_token', 60)->nullable()->unique()->after('can_manage_uniforms');
        });

        // Backfill existing users with qr_token
        $users = DB::table('users')->whereNull('qr_token')->get();
        foreach ($users as $u) {
            DB::table('users')->where('id', $u->id)->update([
                'qr_token' => 'TCH-'.Str::upper(Str::random(12)),
            ]);
        }

        // 2. Create teacher_leaves table
        Schema::create('teacher_leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_setting_id')->constrained('school_settings')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('leave_type', 50)->default('casual'); // casual, medical, annual, half_day, emergency
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });

        // 3. Create teacher_attendances table
        Schema::create('teacher_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_setting_id')->constrained('school_settings')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->enum('status', ['present', 'late', 'on_leave', 'absent'])->default('present');
            $table->text('notes')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['teacher_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_attendances');
        Schema::dropIfExists('teacher_leaves');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('qr_token');
        });
    }
};
