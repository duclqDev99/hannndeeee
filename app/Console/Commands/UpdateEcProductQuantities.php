<?php

namespace App\Console\Commands;

use Botble\Ecommerce\Models\Product;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\Showroom\Models\Showroom;
use Botble\Showroom\Models\ShowroomWarehouse;
use Illuminate\Console\Command;

class UpdateEcProductQuantities extends Command
{
    protected $signature = 'update:ec-product-quantities';

    protected $description = 'Update quantities for all EcProducts based on WfpProductQrcodes';

    public function handle()
    {
        $products = Product::all();
        $qrCode =  ProductQrcode::select(['reference_id','reference_type','status', 'warehouse_type',\DB::raw('count(*) as count')])
        ->where('reference_type', Product::class)
        ->where('status', QRStatusEnum::INSTOCK)
        ->where('warehouse_type' , ShowroomWarehouse::class)
        ->groupBy(['reference_id','reference_type','status', 'warehouse_type'])->get();
        foreach ($products as $product) {
            if(!empty($product->parentProduct) && $product->is_variation == 0)
            {
                $productId = $product->id;
                $filteredQrCode =  $qrCode->filter(function ($item) use ($productId) {
                    return $item->reference->parentProduct->first()->id == $productId;
                });
                $quantity = $filteredQrCode->sum('count');
                $product->update(['quantity' => $quantity]);
            }
            else{
                $quantity =  $qrCode->where('reference_id', $product->id)->first()?->count ?? 0;
                $product->update(['quantity' => $quantity]);
            }

            $this->info("Updated product {$product->id} with quantity {$quantity}");
        }


    }
}
