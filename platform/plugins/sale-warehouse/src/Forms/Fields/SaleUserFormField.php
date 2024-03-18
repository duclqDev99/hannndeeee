<?php

namespace Botble\SaleWarehouse\Forms\Fields;

use Botble\Base\Supports\Editor;
use Illuminate\Support\Arr;
use Kris\LaravelFormBuilder\Fields\FormField;

class SaleUserFormField extends FormField
{
    protected function getTemplate(): string
    {
        return 'plugins/sale-warehouse::custom-fields.sale-user';
    }

   public function render(array $options = [], $showLabel = true, $showField = true, $showError = true, $choices = []): string
   {
       $options['attr'] = Arr::set($options['attr'], 'class', Arr::get($options['attr'], 'class') . 'form-control');

       (new Editor())->registerAssets();
       return parent::render($options, $showLabel, $showField, $showError);
   }
}
