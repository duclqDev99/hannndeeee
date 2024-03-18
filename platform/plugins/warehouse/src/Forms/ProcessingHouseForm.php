<?php

namespace Botble\Warehouse\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Warehouse\Enums\StockStatusEnum;
use Botble\Warehouse\Http\Requests\ProcessingHouseRequest;
use Botble\Warehouse\Models\ProcessingHouse;

class ProcessingHouseForm extends FormAbstract
{
    public function buildForm(): void
    {
        $this
            ->setupModel(new ProcessingHouse)
            ->setValidatorClass(ProcessingHouseRequest::class)
            ->withCustomFields()
            ->add('name', 'text', [
                'label' => trans('plugins/warehouse::material.processing_house.name'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => trans('plugins/warehouse::material.processing_house.name'),
                    'data-counter' => 120,
                ],
            ])

            ->add('phone_number', 'number', [
                'label' => trans('plugins/warehouse::material.processing_house.phone_number'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => trans('plugins/warehouse::material.processing_house.phone_number'),
                    'data-counter' => 120,
                ],
            ])
            ->add('address', 'text', [
                'label' => trans('plugins/warehouse::material.processing_house.address'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => trans('plugins/warehouse::material.processing_house.address'),
                    'data-counter' => 120,
                ],
            ])
            ->add('description', 'textarea', [
                'label' => trans('plugins/warehouse::material.processing_house.description'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder' =>trans('plugins/warehouse::material.processing_house.description'),
                    'data-counter' => 120,
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
