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
        Schema::dropIfExists('finished_accept_delivery');
        Schema::dropIfExists('finished_agency');
        Schema::dropIfExists('finished_branch');
        Schema::dropIfExists('finished_delivery');
        Schema::dropIfExists('finished_products');
        Schema::dropIfExists('finished_product_categories');
        Schema::dropIfExists('finished_proposal_purchase');
        Schema::dropIfExists('finished_receipt');
        Schema::dropIfExists('finished_stocks');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
    }
};
