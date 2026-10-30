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
        Schema::table('classes', function (Blueprint $table) {
            $table->enum('fee_type', ['monthly', 'daily'])->default('monthly')->after('grade_level');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->enum('payment_type', ['monthly', 'daily'])->default('monthly')->after('teacher_id');
            $table->foreignId('collected_by')->nullable()->after('payment_type')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['collected_by']);
            $table->dropColumn(['collected_by', 'payment_type']);
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn('fee_type');
        });
    }
};
