<?php

namespace Botble\Warehouse\Forms\Fields;

use Botble\Base\Forms\FormField;

class MaterialMultiField extends FormField
{
    protected function getTemplate(): string
    {
        return 'plugins/warehouse::custom-fields.material';
    }
}
