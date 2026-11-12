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
        // 1. School settings: multi-tenant SaaS support (owned by user_id/principal) & customizable items to bring checklist
        Schema::table('school_settings', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->cascadeOnDelete();
            $table->string('slug', 80)->nullable()->unique()->after('user_id');
            $table->string('registration_token', 60)->nullable()->unique()->after('slug');
            $table->text('items_to_bring_sinhala')->nullable()->after('principal_message');
            $table->text('items_to_bring_english')->nullable()->after('items_to_bring_sinhala');
        });

        // 2. Link applications to school/tenant
        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('school_setting_id')->nullable()->after('id')->constrained('school_settings')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['school_setting_id']);
            $table->dropColumn('school_setting_id');
        });

        Schema::table('school_settings', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'user_id',
                'slug',
                'registration_token',
                'items_to_bring_sinhala',
                'items_to_bring_english',
            ]);
        });
    }
};
