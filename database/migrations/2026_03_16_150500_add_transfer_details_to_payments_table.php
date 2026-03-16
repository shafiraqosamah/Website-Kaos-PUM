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
            $table->string('destination_bank')->nullable()->after('method');
            $table->string('sender_bank_name')->nullable()->after('destination_bank');
            $table->string('sender_account_name')->nullable()->after('sender_bank_name');
            $table->string('proof_path')->nullable()->after('sender_account_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'destination_bank',
                'sender_bank_name',
                'sender_account_name',
                'proof_path',
            ]);
        });
    }
};
