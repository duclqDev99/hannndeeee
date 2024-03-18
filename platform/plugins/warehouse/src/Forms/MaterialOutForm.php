<?php

namespace Botble\Warehouse\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Warehouse\Enums\BaseStatusEnum;
use Botble\Warehouse\Forms\Fields\MaterialOutField;
use Botble\Warehouse\Http\Requests\MaterialPlanRequest;
use Botble\Warehouse\Models\MaterialWarehouse;
use Botble\Warehouse\Models\MaterialOut;

class MaterialOutForm extends FormAbstract
{
    public function buildForm(): void
    {
        $warehouses = MaterialWarehouse::where('status', 'active')->get()->mapWithKeys(function ($warehouse) {
            $totalQuantity = $warehouse->totalMaterialInStock($warehouse->id);
            return [$warehouse->id => $warehouse->name . ($totalQuantity == 0 ? ' - Kho đã hết nguyên phụ liệu' : '')];
        })->toArray();
        $this
            ->setupModel(new MaterialOut)
            ->setValidatorClass(MaterialPlanRequest::class)
            ->withCustomFields()
            ->addCustomField('material-out-custom-field', MaterialOutField::class)
            ->add('api_key', 'text', [
                'label_show' => false,
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'api_key'
                ],
                'value' => env('API_KEY'),
                'wrapper' => ['class' => 'd-none']
            ])
            ->add('form_title', 'html', [
                'html' => '<h3>Đơn đề xuất xuất kho</h3>'
            ])
            ->add('rowOpen1', 'html', [
                'html' => '<div class="row">'
            ])
            ->add('material_out_id', 'hidden', [
                'value' => $this->model->id,
                'attr' => [
                    'id' => 'material_out_id'
                ]
            ])
            ->add('warehouse_name', 'customSelect', [
                'label' => trans('plugins/warehouse::material_plan.inventory'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-search-full',
                    'id' => 'choose-warehouse',
                ],
                'choices' => ['0' => 'Chọn kho'] + $warehouses,
                'selected' => $this->getModel()->warehouse_old()->pluck('id')->all(),
                'wrapper' => ['class' => 'col-lg-8 col-12'],
            ])
            ->add('general_order_code', 'text', [
                'label' => __('Mã đơn hàng'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nhập mã đơn hàng (nếu có)'
                ],
                'wrapper' => ['class' => 'col-lg-4 col-12']
            ])
            ->add('rowClose1', 'html', [
                'html' => '</div>'
            ])

            ->add(
                'title',
                'text',
                [
                    'label' => trans('Tiêu đề'),
                ],
            )
            ->add('is_processing_house', 'customRadio', [
                'label' => __('Chọn nơi xuất đến'),
                'label_attr' => ['class' => 'control-label'],
                'choices' => [
                    '1' => 'Nhà gia công',
                    '0' => 'Kho khác',

                ],
                'default_value' => 1
            ])

            ->add('nav', 'material-out-custom-field')
            ->add('expected_date', 'datePicker', [
                'label' => trans('Ngày dự kiến'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'class' => 'form-control ',
                    'data-date-format' => 'd-m-Y',
                ],
                'default_value' => date('d-m-Y'),
            ])
            ->add('description', 'textarea', [
                'label' => trans('Ghi chú'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ghi chú',
                    'rows' => 3
                ],
            ])
            ->setBreakFieldPoint('expected_date');
    }
}
