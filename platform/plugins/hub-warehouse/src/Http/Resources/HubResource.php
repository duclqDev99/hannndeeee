<?php


namespace Botble\HubWarehouse\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HubResource extends JsonResource
{
    public function toArray($request)
    {
        $consolidatedProducts = $this->warehouseInHub
            ->flatMap->quantityInstock
            ->groupBy('product_id')
            ->map(function ($items, $productId) {
                $attributes = $items->first()->product->productAttribute->groupBy('attribute_set_title');

                return [
                    'product_id' => $productId,
                    'product_name' => $items->first()->product->name,
                    'quantity' => $items->sum('quantity'),
                    'attribute' => [
                        'size' => $this->mapAttributes($attributes->get('Size')),
                        'color' => $this->mapAttributes($attributes->get('Color')),
                        // Add more attributes as needed
                    ],
                ];
            })
            ->values();

        return [
            'hub_name' => $this->name,
            'products' => $consolidatedProducts,
        ];
    }
    protected function mapAttributes($attributes)
    {
        return $attributes->map(function ($attribute) {
            return [
                'title' => $attribute->title,
                'attribute_set_id' => $attribute->attribute_set_id,
                'attribute_set_title' => $attribute->attribute_set_title,
            ];
        });
    }
}
