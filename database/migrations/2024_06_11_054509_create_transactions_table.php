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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id_transaction')->primary();
            $table->string('order_id');
            $table->uuid('id_user');
            $table->integer('gross_amount');
            $table->enum('transaction_status', ['capture', 'settlement', 'pending', 'deny', 'cancel', 'expire', 'refund']);
            $table->string('fraud_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
