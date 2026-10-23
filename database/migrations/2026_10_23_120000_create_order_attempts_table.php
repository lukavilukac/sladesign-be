<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('checkout_id')->nullable()->index();
            // No cascading foreign keys: history survives deletion of an order/user.
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('status')->index();
            $table->json('payload');
            $table->json('validated_payload')->nullable();
            $table->timestamps();
        });
        Schema::table('activity_log', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->uuid('attempt_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropIndex(['order_id']);
            $table->dropIndex(['attempt_id']);
            $table->dropColumn(['order_id', 'attempt_id']);
        });
        Schema::dropIfExists('order_attempts');
    }
};
