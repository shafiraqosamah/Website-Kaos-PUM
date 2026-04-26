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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('midtrans_order_id')->nullable()->after('order_id');
            $table->string('midtrans_transaction_id')->nullable()->after('midtrans_order_id')->unique();
            $table->string('midtrans_status')->nullable()->after('midtrans_transaction_id');
            $table->string('midtrans_payment_type')->nullable()->after('midtrans_status');
            $table->string('midtrans_fraud_status')->nullable()->after('midtrans_payment_type');
            $table->json('midtrans_response')->nullable()->after('midtrans_fraud_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'midtrans_order_id',
                'midtrans_transaction_id',
                'midtrans_status',
                'midtrans_payment_type',
                'midtrans_fraud_status',
                'midtrans_response',
            ]);
        });
    }
};
