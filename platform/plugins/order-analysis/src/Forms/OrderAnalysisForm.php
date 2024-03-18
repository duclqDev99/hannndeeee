<?php

namespace Botble\OrderAnalysis\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\Assets;
use Botble\OrderAnalysis\Http\Requests\OrderAnalysisRequest;
use Botble\OrderAnalysis\Models\OrderAnalysis;

//add view cusstom
use Botble\OrderAnalysis\Forms\AnalysesProduct\AddAnalysesProduct;

class OrderAnalysisForm extends FormAbstract
{
    public function buildForm(): void
    {


        if(isset($this->model->analysisDetails)){
            $checkSelectSearch = $this->model->analysisDetails->map(function ($detail) {
                return [
                    'quantity' => $detail->quantity,
                    'name' => $detail->material ? $detail->material->name : null,
                    'id' => $detail->material ? $detail->material->id: null,
                    'description' => $detail->material ? $detail->material->description : null,
                    'unit' => $detail->material ? $detail->material->unit : null,
                    'code' => $detail->material ? $detail->material->code : null,
                ];
            });

        };

        $material = $this->formOptions['material'];
        Assets::addScriptsDirectly([
            'vendor/core/plugins/order-analysis/js/add-analysis-product.js',
            'https://unpkg.com/vue-multiselect@2.1.0',
        ])->addStylesDirectly([
            'https://unpkg.com/vue-multiselect@2.1.0/dist/vue-multiselect.min.css',
        ])->removeItemDirectly([
            'vendor/core/core/media/css/media.css'
        ]);
         Assets::usingVueJS();

        if (! $this->formHelper->hasCustomField('AddAnalysesProduct')) {
            $this->formHelper->addCustomField('AddAnalysesProduct', AddAnalysesProduct::class);
        }

        $this
            ->setupModel(new OrderAnalysis)
            ->setValidatorClass(OrderAnalysisRequest::class)
            ->withCustomFields()
            ->add('openHtml3','html',[
                'html' => '<div class="row">'
            ])
                ->add('name', 'text', [
                    'label' => trans('Tên'),
                    'label_attr' => ['class' => 'control-label required'],
                    'attr' => [
                        'placeholder' => trans('Tên'),
                        'data-counter' => 100,
                    ],
                    'wrapper' => [
                        'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ' col-md-6',
                    ],
                ])
                ->add('code', 'text', [
                    'label' => trans('Mã'),
                    'label_attr' => ['class' => 'control-label required'],
                    'attr' => [
                        'placeholder' => trans('Mã'),
                        'data-counter' => 50,
                    ],
                    'wrapper' => [
                        'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ' col-md-6',
                    ],
                ])
            ->add('closeHtml3','html',[
                'html' => '</div>'
            ])
            ->add('description', 'textarea', [
                'label' => trans('Mô tả'),
                'attr' => [
                    'rows' => 4,
                    'placeholder' => trans('Mô tả'),
                    'data-counter' => 400,
                ],
            ])
            ->add('analyses_product_list', 'AddAnalysesProduct', [
                'label' => trans('Danh sách bản thiết kế sản phẩm'),
                'label_attr' => ['class' => 'control-label required'],
                'choices' => $material,
                'value' => isset($checkSelectSearch) ? $checkSelectSearch : [],
            ])
            ;
    }
}
