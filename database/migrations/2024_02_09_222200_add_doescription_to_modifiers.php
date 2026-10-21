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
        Schema::table('modifiers', function (Blueprint $table) {
            $table->string('description')->default('');
        });

        Schema::table('modifier_translations', function (Blueprint $table) {
            $table->string('description')->after('name')->default('');
        });
    }
};
