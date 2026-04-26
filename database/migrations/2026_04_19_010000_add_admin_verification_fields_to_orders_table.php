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
            $table->string('admin_verification_status')->default('pending')->after('order_status');
            $table->text('admin_verification_note')->nullable()->after('admin_verification_status');
            $table->foreignId('admin_verified_by')->nullable()->after('admin_verification_note')->constrained('users')->nullOnDelete();
            $table->timestamp('admin_verified_at')->nullable()->after('admin_verified_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('admin_verified_by');
            $table->dropColumn(['admin_verification_status', 'admin_verification_note', 'admin_verified_at']);
        });
    }
};
