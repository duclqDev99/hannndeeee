<?php

namespace Botble\Warehouse\Forms\Fields;

use Botble\Base\Forms\FormField;

class MaterialOutField extends FormField
{
    protected function getTemplate(): string
    {
        return 'plugins/warehouse::custom-fields.material-out';
    }
}
