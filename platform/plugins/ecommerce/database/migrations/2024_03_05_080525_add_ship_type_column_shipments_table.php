<?php

use Botble\Ecommerce\Enums\ShippingPayerStatusEnum;
use Botble\Ecommerce\Enums\ShippingTypeStatusEnum;
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
        Schema::table('ec_shipments', function (Blueprint $table) {
            $table->string('payer')->after('shipment_id')->default(ShippingPayerStatusEnum::CUSTOMER);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ec_shipments', function (Blueprint $table) {
            $table->dropColumn('payer');
        });
    }
};
