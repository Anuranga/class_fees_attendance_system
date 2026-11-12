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
        // 1. Add permission flags to users (teachers) so Principal can assign fee/uniform collection privileges
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('can_collect_fees')->default(false)->after('teacher_id');
            $table->boolean('can_manage_uniforms')->default(false)->after('can_collect_fees');
        });

        // 2. Uniform & Merchandise items / packages catalog configured by Principal
        Schema::create('uniform_items', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Full Pre-School Uniform Set", "Sports Kit", "Hat & Bag"
            $table->string('size_options')->nullable()->default('XS, S, M, L');
            $table->decimal('price', 10, 2);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Uniform orders & tracking (payment, production, delivery with Sinhala notifications)
        Schema::create('uniform_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('uniform_item_id')->nullable()->constrained('uniform_items')->nullOnDelete();
            $table->string('item_name');
            $table->string('size', 20)->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_amount', 10, 2);

            // Payment tracking
            $table->enum('payment_status', ['pending', 'paid'])->default('pending');
            $table->date('paid_date')->nullable();
            $table->string('receipt_no', 60)->nullable();
            $table->foreignId('collected_by')->nullable()->constrained('users')->nullOnDelete();

            // Fulfillment / Delivery tracking
            $table->enum('delivery_status', ['ordered', 'ready', 'delivered'])->default('ordered');
            $table->date('delivered_date')->nullable();
            $table->foreignId('delivered_by')->nullable()->constrained('users')->nullOnDelete();

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uniform_orders');
        Schema::dropIfExists('uniform_items');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['can_collect_fees', 'can_manage_uniforms']);
        });
    }
};
