<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_item_payment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['payment_id', 'order_item_id']);
            $table->unique('order_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_payment');
    }
};
