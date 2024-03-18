<?php

namespace Botble\WarehouseFinishedProducts\Http\Resources;

use Botble\WarehouseFinishedProducts\Models\ProductBatch;
use Botble\WarehouseFinishedProducts\Models\QuantityProductInStock;
use Botble\WarehouseFinishedProducts\Models\WarehouseFinishedProducts;
use Illuminate\Http\Resources\Json\JsonResource;

class ProposalProductIssueDetailsResource extends JsonResource
{
    public function toArray($request)
    {
        $quantityProductbatch = $this->productBatch->count();
        return [
            'name' => $this->product_name,
            'id' => $this->product_id,
            'sku' => $this->sku,
            'quantity' => $this->quantity,
            'size' => $this->size,
            'color' => $this->color,
            'quantityStock' => $quantityProductbatch > 0 ? $quantityProductbatch : $this->productStock->quantity,
            'is_batch' => $this->is_batch,
            'parent_product' => $this->product?->parentProduct->first(),
            'image' => $this->product?->parentProduct->first()?->image,
        ];
    }
}
