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
        // 1. Tworzenie głównej tabeli płatności
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'card', 'blik', 'other'])->index();
            $table->enum('status', ['pending', 'paid', 'cancelled', 'refunded'])->default('pending')->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        // 2. DOPISANE: Tworzenie tabeli pośredniej z obsługą ilości (quantity)
        // oraz unikalnością dla pary (pozycja + płatność), co pozwala na rozbijanie rachunku
        Schema::create('order_item_payment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('order_items')->onDelete('cascade');
            $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');

            // Kolumna przechowująca informację, za ile sztuk danego dania płaci ta osoba
            $table->integer('quantity')->default(1);

            $table->timestamps();

            // Klucz unikalny na PARĘ. Ta sama pozycja menu może pojawić się w wielu płatnościach!
            $table->unique(['order_item_id', 'payment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Najpierw usuwamy tabelę zależną (pośrednią), potem główną
        Schema::dropIfExists('order_item_payment');
        Schema::dropIfExists('payments');
    }
};
