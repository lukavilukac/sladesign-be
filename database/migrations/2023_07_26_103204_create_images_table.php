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
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->string('title')->nullable();
            $table->string('size')->nullable();

            // Add any additional columns you may need for the product images table

            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            // Add the image_id field as a nullable foreign key referencing the images table
            $table->foreignId('product_image_id')->nullable()->constrained('product_images');
        });

        Schema::create('product_product_image', function (Blueprint $table) {
            // Foreign key for the products table
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');

            // Foreign key for the product_images table
            $table->foreignId('product_image_id')->constrained('product_images')->onDelete('cascade');

            // Define any additional columns for the pivot table (if needed)

            // Create the composite primary key
            $table->primary(['product_id', 'product_image_id']);
        });

        Schema::create('category_images', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->string('title')->nullable();
            $table->string('size')->nullable();

            // Add any additional columns you may need for the product images table

            $table->timestamps();
        });

        Schema::table('categories', function (Blueprint $table) {
            // Add the image_id field as a nullable foreign key referencing the images table
            $table->foreignId('category_image_id')->nullable()->constrained('category_images');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
