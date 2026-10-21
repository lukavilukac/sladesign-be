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
        Schema::dropIfExists('order_line_product_modifiers');

        Schema::create('modifier_order_line', function (Blueprint $table) {
            $table->unsignedBigInteger('modifier_id');
            $table->unsignedBigInteger('order_line_id');
            $table->primary(['modifier_id', 'order_line_id']);

            $table->foreign('modifier_id')->references('id')->on('modifiers');
            $table->foreign('order_line_id')->references('id')->on('order_lines');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
