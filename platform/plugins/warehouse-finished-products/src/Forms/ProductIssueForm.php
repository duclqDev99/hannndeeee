<?php

namespace Botble\WarehouseFinishedProducts\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\WarehouseFinishedProducts\Http\Requests\ProductIssueRequest;
use Botble\WarehouseFinishedProducts\Models\ProductIssue;

class ProductIssueForm extends FormAbstract
{
    public function buildForm(): void
    {
        $this
            ->setupModel(new ProductIssue)
            ->setValidatorClass(ProductIssueRequest::class)
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
