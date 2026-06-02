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
        Schema::table('order_item_payment', function (Blueprint $table) {
            // Dodajemy kolumnę quantity z domyślną wartością 1,
            // aby dotychczasowe (stare) płatności w bazie się nie zepsuły.
            $table->integer('quantity')->default(1)->after('payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_item_payment', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
};
