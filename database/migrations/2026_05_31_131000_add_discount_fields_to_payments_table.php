<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('discount_code_id')
                ->nullable()
                ->after('tip_amount')
                ->constrained('discount_codes')
                ->nullOnDelete();
            $table->decimal('discount_amount', 10, 2)->default(0)->after('discount_code_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('discount_code_id');
            $table->dropColumn('discount_amount');
        });
    }
};
