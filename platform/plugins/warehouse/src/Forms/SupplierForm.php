<?php

namespace Botble\Warehouse\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Warehouse\Http\Requests\SupplierRequest;
use Botble\Warehouse\Models\Supplier;

class SupplierForm extends FormAbstract
{
    public function buildForm(): void
    {
        $this
            ->setupModel(new Supplier)
            ->setValidatorClass(SupplierRequest::class)
            ->withCustomFields()
            ->add('name', 'text', [
                'label' => 'Tên nhà cung cấp',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => 'Tên nhà cung cấp',
                    'data-counter' => 120,
                ],
            ])
            ->add('address', 'text', [
                'label' => trans('plugins/warehouse::supplier.address'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => trans('plugins/warehouse::supplier.address'),
                ],
            ])
            ->add('phone_number', 'number', [
                'label' =>  __('Số điện thoại'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => __('Nhập số điện thoại'),
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
                'label' => __('Trạng thái'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-full',
                ],
                'choices' => BaseStatusEnum::labels(),
            ])
            ->setBreakFieldPoint('status');
    }
}
