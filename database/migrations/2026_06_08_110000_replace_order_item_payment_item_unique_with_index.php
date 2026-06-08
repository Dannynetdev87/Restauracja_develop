<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_item_payment', function (Blueprint $table) {
            $table->dropUnique('order_item_payment_order_item_id_unique');
            $table->index('order_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('order_item_payment', function (Blueprint $table) {
            $table->dropIndex('order_item_payment_order_item_id_index');
            $table->unique('order_item_id');
        });
    }
};
