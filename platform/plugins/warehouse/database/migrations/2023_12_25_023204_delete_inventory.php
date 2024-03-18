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
        Schema::dropIfExists('import_export_materials');
        Schema::dropIfExists('inventory_materials');
        Schema::dropIfExists('inventory_materials_translations');
        Schema::dropIfExists('in_out_detail');
        Schema::dropIfExists('materials');
        Schema::dropIfExists('materials_translations');
        Schema::dropIfExists('material_plans');
        Schema::dropIfExists('material_typematerial');
        Schema::dropIfExists('quantity_material_in_out');
        Schema::dropIfExists('quantity_material_plan');
        Schema::dropIfExists('quantity_material_stock');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('suppliers_translations');
        Schema::dropIfExists('type_materials_translations');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('warehouses_translations');
        Schema::dropIfExists('check_inventories');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
