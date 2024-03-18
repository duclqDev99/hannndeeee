<?php

namespace Botble\OrderAnalysis\Forms\AnalysesProduct;

use Botble\Base\Supports\Editor;
use Illuminate\Support\Arr;
use Kris\LaravelFormBuilder\Fields\FormField;

class AddAnalysesProduct extends FormField
{
    protected function getTemplate(): string
    {
        return 'plugins/order-analysis::analysisProduct.add-analysis-product';
    }

   public function render(array $options = [], $showLabel = true, $showField = true, $showError = true, $choices = []): string
   {
       $options['attr'] = Arr::set($options['attr'], 'class', Arr::get($options['attr'], 'class') . 'form-control');

       (new Editor())->registerAssets();
       return parent::render($options, $showLabel, $showField, $showError);
   }
}
