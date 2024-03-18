<?php

namespace Botble\Agent\Http\Resources;

use Botble\Agent\Models\AgentProduct;
use Illuminate\Http\Resources\Json\JsonResource;

class AgentProposalIssueResource extends JsonResource
{
    public function toArray($request)
    {
        $quantityStock = AgentProduct::where(['warehouse_id' => $this->warehouse_id, 'product_id' => $this->product_id])->first();
        return [
            'name' => $this->product_name,
            'id' => $this->product_id,
            'sku' => $this->sku,
            'quantity' => $this->quantity,
            'size' => $this->size,
            'color' => $this->color,
            'quantityStock' => $quantityStock->quantity_qrcode,
            'is_batch' => 0,
            'image' => $quantityStock->product[0]->images[0]

        ];
    }
}
