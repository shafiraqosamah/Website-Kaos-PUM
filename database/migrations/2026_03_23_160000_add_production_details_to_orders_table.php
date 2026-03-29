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
            $table->string('production_type')->nullable()->after('fabric');
            $table->string('product_model')->nullable()->after('production_type');
            $table->string('sleeve_type')->nullable()->after('product_model');
            $table->renameColumn('design_path', 'design_file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['production_type', 'product_model', 'sleeve_type']);
            $table->renameColumn('design_file', 'design_path');
        });
    }
};
