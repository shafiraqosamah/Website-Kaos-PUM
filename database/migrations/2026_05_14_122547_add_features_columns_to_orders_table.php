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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('secondary_color')->nullable()->after('dominant_color');
            $table->timestamp('payment_deadline_at')->nullable()->after('admin_verified_at');
            $table->timestamp('revision_requested_at')->nullable()->after('payment_deadline_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['secondary_color', 'payment_deadline_at', 'revision_requested_at']);
        });
    }
};
