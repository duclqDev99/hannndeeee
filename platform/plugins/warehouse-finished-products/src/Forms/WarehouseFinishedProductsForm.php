<?php

namespace Botble\WarehouseFinishedProducts\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\WarehouseEnum;
use Botble\WarehouseFinishedProducts\Http\Requests\WarehouseFinishedProductsRequest;
use Botble\WarehouseFinishedProducts\Models\WarehouseFinishedProducts;

class WarehouseFinishedProductsForm extends FormAbstract
{
    public function buildForm(): void
    {
        $this
            ->setupModel(new WarehouseFinishedProducts)
            ->setValidatorClass(WarehouseFinishedProductsRequest::class)
            ->withCustomFields()
            ->add('name', 'text', [
                'label' => trans('Tên kho'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => 'Tên kho',
                    'data-counter' => 120,
                ],
            ])
            ->add('address', 'text', [
                'label' => trans('Địa chỉ'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => trans('plugins/warehouse::supplier.address'),
                ],
            ])
            ->add('phone_number', 'number', [
                'label' => trans('Số điện thoại'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => __('Nhập số điện thoại'),
                ],
            ])
            ->add('description', 'textarea', [
                'label' => trans('Mô tả'),
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
                'choices' => WarehouseEnum::labels(),
            ])
            ->setBreakFieldPoint('status');
    }
}
