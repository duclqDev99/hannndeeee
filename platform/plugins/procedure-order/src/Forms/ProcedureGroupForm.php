<?php

namespace Botble\ProcedureOrder\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\ProcedureOrder\Http\Requests\ProcedureGroupRequest;
use Botble\ProcedureOrder\Models\ProcedureGroup;

class ProcedureGroupForm extends FormAbstract
{
    public function buildForm(): void
    {
        $this
            ->setupModel(new ProcedureGroup)
            ->setValidatorClass(ProcedureGroupRequest::class)
            ->withCustomFields()
            ->add('openHtml3','html',[
                'html' => '<div class="row">'
            ])
                ->add('name', 'text', [
                    'label' => trans('core/base::forms.name'),
                    'label_attr' => ['class' => 'control-label required'],
                    'attr' => [
                        'placeholder' => trans('core/base::forms.name_placeholder'),
                        'data-counter' => 120,
                    ],
                    'wrapper' => [
                        'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ' col-md-6',
                    ],
                ])
                ->add('code', 'text', [
                    'label' => trans('Code'),
                    'label_attr' => ['class' => 'control-label required'],
                    'attr' => [
                        'placeholder' => trans('Code'),
                        'data-counter' => 120,
                    ],
                    'wrapper' => [
                        'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ' col-md-6',
                    ],
                ])
            ->add('closeHtml3','html',[
                'html' => '</div>'
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
