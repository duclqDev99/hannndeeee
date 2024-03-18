<?php

namespace Botble\WarehouseFinishedProducts\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\HubStatusEnum;
use Botble\WarehouseFinishedProducts\Http\Requests\HubRequest;
use Botble\WarehouseFinishedProducts\Models\Hub;

class HubForm extends FormAbstract
{
    public function buildForm(): void
    {
        $this
            ->setupModel(new Hub)
            ->setValidatorClass(HubRequest::class)
            ->withCustomFields()
            ->add('name', 'text', [
                'label' => trans('Tên HUB'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => trans('core/base::forms.name_placeholder'),
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
                'choices' => HubStatusEnum::labels(),
            ])
            ->setBreakFieldPoint('status');
    }
}
