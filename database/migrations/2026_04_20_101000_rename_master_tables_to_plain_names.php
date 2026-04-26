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
        Schema::table('catalog_products', function (Blueprint $table) {
            $table->dropForeign(['material_id']);
            $table->dropForeign(['production_type_id']);
            $table->dropForeign(['product_model_id']);
            $table->dropForeign(['sleeve_type_id']);
            $table->dropForeign(['color_id']);
        });

        Schema::rename('master_materials', 'materials');
        Schema::rename('master_production_types', 'production_types');
        Schema::rename('master_design_positions', 'design_positions');
        Schema::rename('master_product_models', 'product_models');
        Schema::rename('master_sleeve_types', 'sleeve_types');
        Schema::rename('master_sizes', 'sizes');
        Schema::rename('master_colors', 'colors');

        Schema::table('catalog_products', function (Blueprint $table) {
            $table->foreign('material_id')->references('id')->on('materials')->nullOnDelete();
            $table->foreign('production_type_id')->references('id')->on('production_types')->nullOnDelete();
            $table->foreign('product_model_id')->references('id')->on('product_models')->nullOnDelete();
            $table->foreign('sleeve_type_id')->references('id')->on('sleeve_types')->nullOnDelete();
            $table->foreign('color_id')->references('id')->on('colors')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalog_products', function (Blueprint $table) {
            $table->dropForeign(['material_id']);
            $table->dropForeign(['production_type_id']);
            $table->dropForeign(['product_model_id']);
            $table->dropForeign(['sleeve_type_id']);
            $table->dropForeign(['color_id']);
        });

        Schema::rename('materials', 'master_materials');
        Schema::rename('production_types', 'master_production_types');
        Schema::rename('design_positions', 'master_design_positions');
        Schema::rename('product_models', 'master_product_models');
        Schema::rename('sleeve_types', 'master_sleeve_types');
        Schema::rename('sizes', 'master_sizes');
        Schema::rename('colors', 'master_colors');

        Schema::table('catalog_products', function (Blueprint $table) {
            $table->foreign('material_id')->references('id')->on('master_materials')->nullOnDelete();
            $table->foreign('production_type_id')->references('id')->on('master_production_types')->nullOnDelete();
            $table->foreign('product_model_id')->references('id')->on('master_product_models')->nullOnDelete();
            $table->foreign('sleeve_type_id')->references('id')->on('master_sleeve_types')->nullOnDelete();
            $table->foreign('color_id')->references('id')->on('master_colors')->nullOnDelete();
        });
    }
};
