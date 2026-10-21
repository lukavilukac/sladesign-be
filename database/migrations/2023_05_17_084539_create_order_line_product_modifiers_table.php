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
        Schema::create('order_line_product_modifiers', function (Blueprint $table) {
            $table->id();//todo - not sure if needed
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('order_line_id');
            $table->unsignedBigInteger('modifier_id');
            $table->unique(['product_id', 'order_line_id', 'modifier_id'], 'o_l_p_m_unique');

            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('order_line_id')->references('id')->on('order_lines');
            $table->foreign('modifier_id')->references('id')->on('modifiers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_line_product_modifiers');
    }
};
