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
        Schema::create('addresses', function (Blueprint $table) {
            $table->uuid('id_address')->primary();
            $table->uuid('id_user')->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
            $table->string('province');
            $table->string('city');
            $table->string('zip_code');
            $table->string('details');
            $table->integer('shipping_cost')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
