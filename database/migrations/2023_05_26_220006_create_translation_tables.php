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
        Schema::create('product_translations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('locale');
            $table->string('slug');
            $table->unsignedBigInteger('product_id');

            $table->foreign('product_id')->references('id')->on('products');
            $table->index('locale');
            $table->index('slug');
            $table->index('name');
        });

        Schema::create('modifier_group_translations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('locale');
            $table->unsignedBigInteger('modifier_group_id');

            $table->foreign('modifier_group_id')->references('id')->on('modifier_groups');
            $table->index('locale');
            $table->index('slug');
            $table->index('name');
        });

        Schema::create('modifier_translations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('locale');
            $table->unsignedBigInteger('modifier_id');

            $table->foreign('modifier_id')->references('id')->on('modifiers');
            $table->index('locale');
            $table->index('slug');
            $table->index('name');
        });

        Schema::create('category_translations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('locale');
            $table->text('description');
            $table->unsignedBigInteger('category_id');

            $table->foreign('category_id')->references('id')->on('categories');
            $table->index('locale');
            $table->index('slug');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translation_tables');
    }
};
