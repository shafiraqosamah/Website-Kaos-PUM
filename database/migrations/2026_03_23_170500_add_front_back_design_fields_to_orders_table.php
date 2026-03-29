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
            $table->string('design_front_file')->nullable()->after('design_file');
            $table->string('design_back_file')->nullable()->after('design_front_file');
            $table->text('design_notes')->nullable()->after('design_back_file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['design_front_file', 'design_back_file', 'design_notes']);
        });
    }
};
