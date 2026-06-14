<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('payment_status')->default('unpaid')->after('value')->index();
            $table->decimal('paid_amount', 12, 2)->nullable()->after('payment_status');
            $table->string('fulfillment_status')->default('pending')->after('paid_amount')->index();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'paid_amount', 'fulfillment_status']);
        });
    }
};
