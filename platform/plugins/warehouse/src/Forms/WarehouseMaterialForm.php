<?php

namespace Botble\Warehouse\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Warehouse\Enums\StockStatusEnum;
use Botble\Warehouse\Http\Requests\WarehouseMaterialRequest;
use Botble\Warehouse\Models\MaterialWarehouse;

class WarehouseMaterialForm extends FormAbstract
{
    public function buildForm(): void
    {
        $this
            ->setupModel(new MaterialWarehouse)
            ->setValidatorClass(WarehouseMaterialRequest::class)
            ->withCustomFields()
            ->add('id','hidden', [
                'value' => $this->model->id,
            ])
            ->add('name', 'text', [
                'label' => 'Tên kho',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => 'Tên kho',
                    'data-counter' => 120,
                ],
            ])
            ->add('phone_number', 'number', [
                'label' => 'Số điện thoại',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' =>'Số điện thoại',
                    'data-counter' => 120,
                ],
            ])
            ->add('address', 'text', [
                'label' => 'Địa chỉ',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => 'Đia chỉ',
                    'data-counter' => 120,
                ],
            ])
            ->add('description', 'textarea', [
                'label' =>  __('Mô tả'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder' => __('Mô tả'),
                ],
            ])
            ->add('status', 'customSelect', [
                'label' => trans('core/base::tables.status'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-full',
                ],
                'choices' => StockStatusEnum::labels(),
            ])
            ->setBreakFieldPoint('status');
    }
}
