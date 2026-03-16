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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->after('order_code');
        });

        DB::table('orders')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->update(['orders.customer_name' => DB::raw('users.name')]);

        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_name')->nullable(false)->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('invoice_number')->nullable()->after('method');
            $table->timestamp('invoiced_at')->nullable()->after('invoice_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('customer_name');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['invoice_number', 'invoiced_at']);
        });
    }
};
