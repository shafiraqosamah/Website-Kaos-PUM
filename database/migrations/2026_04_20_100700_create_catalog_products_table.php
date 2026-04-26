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
        Schema::create('catalog_products', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 170)->unique();
            $table->string('name', 160);
            $table->string('category', 120)->nullable();
            $table->string('image_path')->nullable();
            $table->text('short_description')->nullable();
            $table->unsignedInteger('unit_price')->nullable();
            $table->unsignedInteger('minimum_order_qty')->default(60);
            $table->foreignId('material_id')->nullable()->constrained('master_materials')->nullOnDelete();
            $table->foreignId('production_type_id')->nullable()->constrained('master_production_types')->nullOnDelete();
            $table->foreignId('product_model_id')->nullable()->constrained('master_product_models')->nullOnDelete();
            $table->foreignId('sleeve_type_id')->nullable()->constrained('master_sleeve_types')->nullOnDelete();
            $table->foreignId('color_id')->nullable()->constrained('master_colors')->nullOnDelete();
            $table->text('design_notes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_products');
    }
};
