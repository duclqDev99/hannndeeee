<?php

namespace Botble\WarehouseFinishedProducts\Forms\Fields;

use Botble\Base\Forms\FormField;

class ProposalProductField extends FormField
{
    protected function getTemplate(): string
    {
        return 'plugins/warehouse-finished-products::custom-fields.proposal-product';
    }
}
