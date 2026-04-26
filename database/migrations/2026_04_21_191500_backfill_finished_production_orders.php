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
            UPDATE orders o
            JOIN (
                SELECT
                    order_id,
                    COUNT(*) AS total_steps,
                    SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) AS done_steps
                FROM production_steps
                GROUP BY order_id
            ) ps ON ps.order_id = o.id
            SET o.order_status = 'production_done_waiting_admin'
            WHERE ps.total_steps = ps.done_steps
              AND o.order_status IN ('in_production', 'finishing_waiting_settlement')
              AND o.remaining_amount <= 0
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
            WHERE order_status = 'production_done_waiting_admin'
        SQL);
    }
};
