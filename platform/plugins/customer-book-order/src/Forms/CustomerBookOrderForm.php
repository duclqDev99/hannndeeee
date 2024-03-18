<?php

namespace Botble\CustomerBookOrder\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\CustomerBookOrder\Http\Requests\CustomerBookOrderRequest;
use Botble\CustomerBookOrder\Models\CustomerBookOrder;

class CustomerBookOrderForm extends FormAbstract
{
    public function buildForm(): void
    {
        $this
            ->setupModel(new CustomerBookOrder)
            ->setValidatorClass(CustomerBookOrderRequest::class)
            ->withCustomFields()
            ->add('name', 'text', [
                'label' => trans('core/base::forms.name'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => trans('core/base::forms.name_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->add('status', 'customSelect', [
                'label' => trans('core/base::tables.status'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-full',
                ],
                'choices' => BaseStatusEnum::labels(),
            ])
            ->setBreakFieldPoint('status');
    }
}
