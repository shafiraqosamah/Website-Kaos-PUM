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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('order_code')->unique();
            $table->string('customer_name');
            $table->string('product_name')->default('Kaos Custom');
            $table->unsignedInteger('total_pcs');
            $table->string('fabric');
            $table->string('dominant_color');
            $table->string('design_path')->nullable();
            $table->date('estimated_finish_date');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 14, 2);
            $table->enum('payment_type', ['dp', 'full'])->default('dp');
            $table->decimal('dp_amount', 14, 2)->default(0);
            $table->decimal('remaining_amount', 14, 2)->default(0);
            $table->enum('payment_status', ['pending_verification', 'verified_dp', 'fully_paid'])->default('pending_verification');
            $table->enum('order_status', ['submitted', 'verified_payment', 'in_production', 'finishing_waiting_settlement', 'completed'])->default('submitted');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
