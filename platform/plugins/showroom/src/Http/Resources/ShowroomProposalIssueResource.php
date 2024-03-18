<?php

namespace Botble\Showroom\Http\Resources;

use Botble\Showroom\Models\ShowroomProduct;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowroomProposalIssueResource extends JsonResource
{
    public function toArray($request)
    {
        $quantityStock = ShowroomProduct::where(['warehouse_id' => $this->warehouse_id, 'product_id' => $this->product_id])->first();
        return [
            'name' => $this->product_name,
            'id' => $this->product_id,
            'sku' => $this->sku,
            'quantity' => $this->quantity,
            'size' => $this->size,
            'color' => $this->color,
            'quantityStock' => $quantityStock->quantity_qrcode,
            'is_batch' => 0,
            'image' => $quantityStock->product->parentProduct[0]->image,
            'parent_product' => $quantityStock->product->parentProduct,
        ];
    }
}
