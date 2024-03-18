<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $viewExists = DB::table('information_schema.views')
            ->where('table_schema', env('DB_DATABASE'))
            ->where('table_name', 'showroom_order_view_ec')
            ->exists();

        if (!$viewExists) {
            DB::statement("
                CREATE VIEW showroom_order_view_ec AS
                SELECT
                    e.id,
                    e.code,
                    e.status,
                    e.user_id,
                    e.amount,
                    e.tax_amount,
                    e.shipping_amount,
                    e.discount_amount,
                    e.payment_id,
                    e.sub_total,
                    e.description,
                    e.created_at,
                    s.where_type,
                    s.where_id
                FROM ec_orders e
                INNER JOIN showroom_orders s ON e.id = s.order_id
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS showroom_order_view_ec');
    }
};
