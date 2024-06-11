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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id_product')->primary();
            $table->string('name_product', 50);
            $table->text('desc');
            $table->string('brand', 50);
            $table->longText('image');
            $table->longText('image2')->nullable();
            $table->longText('image3')->nullable();
            $table->integer('size');
            $table->integer('price');
            $table->integer('discount');
            $table->enum('status', ['available', 'sold out'])->default('available');            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
