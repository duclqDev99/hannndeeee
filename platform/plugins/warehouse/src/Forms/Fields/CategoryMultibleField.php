<?php

namespace Botble\Warehouse\Forms\Fields;

use Botble\Base\Forms\FormField;

class CategoryMultibleField extends FormField
{
    protected function getTemplate(): string
    {
        return 'plugins/warehouse::categories.categories-multi';
    }
}
