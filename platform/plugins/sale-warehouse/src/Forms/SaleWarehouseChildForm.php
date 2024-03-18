<?php

namespace Botble\SaleWarehouse\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\SaleWarehouse\Enums\SaleWarehouseStatusEnum;
use Botble\SaleWarehouse\Http\Requests\SaleWarehouseChildRequest;
use Botble\SaleWarehouse\Models\SaleWarehouse;
use Botble\SaleWarehouse\Models\SaleWarehouseChild;

class SaleWarehouseChildForm extends FormAbstract
{
    public function buildForm(): void
    {
        $saleWarehouse = SaleWarehouse::query()->pluck('name', 'id')->all();
        $this
            ->setupModel(new SaleWarehouseChild)
            ->setValidatorClass(SaleWarehouseChildRequest::class)
            ->withCustomFields()
            ->add('sale_warehouse_id', 'customSelect', [
                'label' => trans('Kho sale'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-full',
                ],
                'choices' =>  $saleWarehouse,
            ])
            ->add('name', 'text', [
                'label' => trans('core/base::forms.name'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => trans('core/base::forms.name_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->add('phone', 'number', [
                'label' => trans('Số điện thoại'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder' => trans('Số điện thoại'),
                    'data-counter' => 120,
                ],
            ])
            ->add('address', 'text', [
                'label' => trans('Địa chỉ'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder' => trans('Địa chỉ'),
                    'data-counter' => 120,
                ],
            ])
            ->add('description', 'textarea', [
                'label' => trans('Mô tả'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder' => trans('Mô tả'),
                    'data-counter' => 120,
                ],
            ])
            ->add('status', 'customSelect', [
                'label' => trans('core/base::tables.status'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-full',
                ],
                'choices' => SaleWarehouseStatusEnum::labels(),
            ])
            ->setBreakFieldPoint('status');
    }
}
