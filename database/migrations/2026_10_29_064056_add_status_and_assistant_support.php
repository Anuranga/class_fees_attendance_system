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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('teacher')->change();
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active')->after('role');
            $table->foreignId('teacher_id')->nullable()->after('status')->constrained('users')->cascadeOnDelete();
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->boolean('tute_provided')->default(false)->after('status');
            $table->string('tute_name')->nullable()->after('tute_provided');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['tute_provided', 'tute_name']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropColumn(['teacher_id', 'status']);
        });
    }
};
