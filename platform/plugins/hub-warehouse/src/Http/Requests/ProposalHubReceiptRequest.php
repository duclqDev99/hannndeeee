<?php

namespace Botble\HubWarehouse\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum;
use Illuminate\Validation\Rule;

class ProposalHubReceiptRequest extends Request
{
    public function prepareForValidation()
    {
        if ($this->has('quantity') && is_array($this->input('quantity'))) {
            $quantity = $this->input('quantity');
            array_shift($quantity);
            $this->merge([
                'quantity' => $quantity,
            ]);
        }
        if ($this->has('price') && is_array($this->input('price'))) {
            $price = $this->input('price');
            array_shift($price);
            $this->merge([
                'price' => $price,
            ]);
        }
        if ($this->has('product') && is_array($this->input('product'))) {
            $product = $this->input('product');
            array_shift($product);
            $this->merge([
                'product' => $product,
            ]);
        }
        if ($this->has('quantityStock') && is_array($this->input('quantityStock'))) {
            $quantityStock = $this->input('quantityStock');
            array_shift($quantityStock);
            $this->merge([
                'quantityStock' => $quantityStock,
            ]);
        }
    }
    public function rules(): array
    {
        return [
            "warehouse_receipt_id" => 'required|gt:0',
            "hub_id" => 'required|gt:0',
            'warehouse_product' => 'required_if:is_warehouse,0',
            'hub' => 'required_if:is_warehouse,1',
            'warehouseHub' => 'required_if:is_warehouse,1',
            'warehouse_out' => 'required_if:is_warehouse,2',
            'expected_date' => 'required|date|after:yesterday',
            'title' => 'required',
            'status' => Rule::in(ProposalProductEnum::values()),
            'quantityBatch.*.quantity' => 'required|numeric|min:1',
            'quantityBatch' => 'required',
        ];

    }
}
