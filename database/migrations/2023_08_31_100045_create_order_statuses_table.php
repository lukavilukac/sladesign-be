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
        Schema::create('order_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\DB::table('order_statuses')->insert([
            [
                'id' => 1,
                'title' => 'Pending'
            ],
            [
                'id' => 2,
                'title' => 'In Progress'
            ],
            [
                'id' => 3,
                'title' => 'Sent'
            ],
            [
                'id' => 4,
                'title' => 'Done'
            ],
            [
                'id' => 5,
                'title' => 'Failed'
            ],
        ]);

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('order_status_id')->default(1);
            $table->foreign('order_status_id')->references('id')->on('order_statuses');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_statuses');
    }
};
