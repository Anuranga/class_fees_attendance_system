<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('school_setting_id')->nullable()->after('id')->constrained('school_settings')->nullOnDelete();
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->foreignId('school_setting_id')->nullable()->after('id')->constrained('school_settings')->nullOnDelete();
        });

        Schema::table('uniform_items', function (Blueprint $table) {
            $table->foreignId('school_setting_id')->nullable()->after('id')->constrained('school_settings')->nullOnDelete();
        });

        // Backfill existing records with first school if present
        $firstSchool = DB::table('school_settings')->first();
        if ($firstSchool) {
            DB::table('users')->whereNull('school_setting_id')->update(['school_setting_id' => $firstSchool->id]);
            DB::table('classes')->whereNull('school_setting_id')->update(['school_setting_id' => $firstSchool->id]);
            DB::table('uniform_items')->whereNull('school_setting_id')->update(['school_setting_id' => $firstSchool->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('uniform_items', function (Blueprint $table) {
            $table->dropForeign(['school_setting_id']);
            $table->dropColumn('school_setting_id');
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['school_setting_id']);
            $table->dropColumn('school_setting_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['school_setting_id']);
            $table->dropColumn('school_setting_id');
        });
    }
};
