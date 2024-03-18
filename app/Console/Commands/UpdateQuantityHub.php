<?php

namespace App\Console\Commands;

use Botble\Ecommerce\Models\Product;
use Botble\HubWarehouse\Models\QuantityProductInStock;
use Botble\HubWarehouse\Models\Warehouse;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\ProductQrcode\Models\ProductQrcode;
use Illuminate\Console\Command;

class UpdateQuantityHub extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-quantity-hub';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $productInStocks = QuantityProductInStock::all();
        foreach ($productInStocks as $productInStock) {
                $quantity = ProductQrcode::where('reference_id', $productInStock->product_id)
                    ->where('reference_type', Product::class)
                    ->whereIn('status', [QRStatusEnum::INSTOCK, QRStatusEnum::PENDING])
                    ->where('warehouse_type' , Warehouse::class)
                    ->where('warehouse_id' , $productInStock->stock_id)
                    ->count();

                // $quantitySold = ProductQrcode::where('reference_id', $productInStock->product_id)
                //     ->where('reference_type', Product::class)
                //     ->where('status', QRStatusEnum::SOLD)
                //     ->where('warehouse_type' , Warehouse::class)
                //     ->where('warehouse_id' , $productInStock->stock_id)
                //     ->count();

                $productInStock->update(['quantity' => $quantity]);


            $this->info("Updated product {$productInStock->id} with quantity {$quantity}");
        }
    }
}
