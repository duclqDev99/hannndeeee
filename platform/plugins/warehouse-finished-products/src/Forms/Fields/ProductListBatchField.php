<?php

namespace Botble\WarehouseFinishedProducts\Forms\Fields;

use Botble\Base\Forms\FormField;

class ProductListBatchField extends FormField
{
    protected function getTemplate(): string
    {
        return 'plugins/warehouse-finished-products::custom-fields.list-branch-warehouse';
    }
}
