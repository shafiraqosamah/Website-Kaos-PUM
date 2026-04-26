<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(<<<'SQL'
            ALTER TABLE orders
            MODIFY COLUMN order_status ENUM(
                'submitted',
                'pending_verification',
                'verified_dp',
                'verified_payment',
                'in_production',
                'finishing_waiting_settlement',
                'production_done_waiting_admin',
                'completed',
                'rejected'
            ) NOT NULL DEFAULT 'submitted'
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement(<<<'SQL'
            UPDATE orders
            SET order_status = 'in_production'
            WHERE order_status IN ('pending_verification', 'verified_dp', 'production_done_waiting_admin', 'rejected')
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE orders
            MODIFY COLUMN order_status ENUM(
                'submitted',
                'verified_payment',
                'in_production',
                'finishing_waiting_settlement',
                'completed'
            ) NOT NULL DEFAULT 'submitted'
        SQL);
    }
};
