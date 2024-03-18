<?php

namespace Botble\Sales\Http\Resources;

use Botble\Sales\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 */
class SampleProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'price' => $this->price,
            'description' => "",
            'final_price' => $this->price,
            'formatted_price' => format_price($this->price),
            'height' => 123,
            'image_url' => "http://127.0.0.1:8000/vendor/core/core/base/images/placeholder.png",
            'image_with_sizes' => '',
            'is_out_of_stock' => false,
            'is_variation' => 0,
            'length' => 12,
            'original_price' => $this->price,
            'original_product_id' => $this->id,
            'product_options' => [],
            'product_link' => '',
            'quantity' => 100,
            'slug' => '',
            'tax_price' => 10,
            'total_taxes_percentage' => 10,
            'variations' => [],
            'weight' => 131,
            'wide' => 31,
            'with_storehouse_management' => 1,
            'stock_status_label' => 'In stock',
            'stock_status_html' => '<span class=\"text-success\">In stock</span>',
            'is_sample' => true
        ];
    }
}
