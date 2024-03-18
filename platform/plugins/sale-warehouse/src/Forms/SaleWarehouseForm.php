<?php

namespace Botble\SaleWarehouse\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\HubWarehouse\Models\HubWarehouse;
use Botble\SaleWarehouse\Enums\SaleWarehouseStatusEnum;
use Botble\SaleWarehouse\Http\Requests\SaleWarehouseRequest;
use Botble\SaleWarehouse\Models\SaleWarehouse;

class SaleWarehouseForm extends FormAbstract
{
    public function buildForm(): void
    {
        $hub = HubWarehouse::query()->pluck('name', 'id')->all();
        $this
            ->setupModel(new SaleWarehouse)
            ->setValidatorClass(SaleWarehouseRequest::class)
            ->withCustomFields()
            ->add('hub_id', 'customSelect', [
                'label' => 'HUB',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-full',
                ],
                'choices' =>  $hub,
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
