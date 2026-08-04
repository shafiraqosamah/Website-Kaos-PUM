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
        Schema::table('materials', function (Blueprint $table) {
            $table->string('title')->nullable()->after('slug');
            $table->text('description')->nullable()->after('title');
            $table->string('image_path')->nullable()->after('description');
            $table->json('tags')->nullable()->after('image_path');
            $table->json('suitable_for')->nullable()->after('tags');
            $table->json('design_application')->nullable()->after('suitable_for');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn([
                'title',
                'description',
                'image_path',
                'tags',
                'suitable_for',
                'design_application',
            ]);
        });
    }
};
