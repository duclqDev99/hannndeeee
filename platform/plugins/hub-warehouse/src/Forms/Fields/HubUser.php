<?php

namespace Botble\HubWarehouse\Forms\Fields;

use Botble\Base\Supports\Editor;
use Illuminate\Support\Arr;
use Kris\LaravelFormBuilder\Fields\FormField;

class HubUser extends FormField
{
    protected function getTemplate(): string
    {
        return 'plugins/hub-warehouse::custom-fields.add-user-hub';
    }

   public function render(array $options = [], $showLabel = true, $showField = true, $showError = true, $choices = []): string
   {
       $options['attr'] = Arr::set($options['attr'], 'class', Arr::get($options['attr'], 'class') . 'form-control');

       (new Editor())->registerAssets();
       return parent::render($options, $showLabel, $showField, $showError);
   }
}
