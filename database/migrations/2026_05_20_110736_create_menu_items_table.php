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
        if (Schema::hasTable('menu_items')) {
            Schema::table('menu_items', function (Blueprint $table) {
                if (Schema::hasColumn('menu_items', 'is_available') && ! Schema::hasColumn('menu_items', 'available')) {
                    $table->renameColumn('is_available', 'available');
                }

                if (! Schema::hasColumn('menu_items', 'menu_category_id')) {
                    $table->foreignId('menu_category_id')->nullable()->constrained('menu_categories')->restrictOnDelete();
                }

                if (! Schema::hasColumn('menu_items', 'production_area')) {
                    $table->enum('production_area', ['kuchnia', 'bar'])->default('kuchnia')->index();
                }
            });

            return;
        }

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_category_id')->constrained('menu_categories')->restrictOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->enum('production_area', ['kuchnia', 'bar'])->index();
            $table->boolean('available')->default(true)->index();
            $table->timestamps();

            $table->unique(['menu_category_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
