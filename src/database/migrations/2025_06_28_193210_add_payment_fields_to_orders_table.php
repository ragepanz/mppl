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
        Schema::table('orders', function (Blueprint $table) {
            $table->text('payment_notes')->nullable()->after('payment_proof');
            $table->timestamp('payment_verified_at')->nullable()->after('payment_notes');
            $table->timestamp('payment_rejected_at')->nullable()->after('payment_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_notes',
                'payment_verified_at',
                'payment_rejected_at'
            ]);
        });
    }
};
