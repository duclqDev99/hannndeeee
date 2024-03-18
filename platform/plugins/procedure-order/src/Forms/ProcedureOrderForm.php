<?php

namespace Botble\ProcedureOrder\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\ProcedureOrder\Http\Requests\ProcedureOrderRequest;
use Botble\ProcedureOrder\Models\ProcedureOrder;

class ProcedureOrderForm extends FormAbstract
{
    public function buildForm(): void
    {

        $groupIdValue = request()->input('id');
        $groupConfig = [
            'wrapper' => [
                'class' => 'hidden',
            ],
        ];
        if (!empty($groupIdValue)) {
            $groupConfig['value'] = $groupIdValue;
        }
        $this
            ->setupModel(new ProcedureOrder)
            ->setValidatorClass(ProcedureOrderRequest::class)
            ->withCustomFields()
            ->add('group_id', 'text', $groupConfig)
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
                    'label' => trans('Mã'),
                    'label_attr' => ['class' => 'control-label required'],
                    'attr' => [
                        'placeholder' => trans('Code'),
                        'data-counter' => 120,
                    ],
                    'wrapper' => [
                        'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ' col-md-3',
                    ],
                ])
                ->add('parent_id', 'text', [
                    'label' => trans('Nhanh phụ thuộc'),
                    'label_attr' => ['class' => 'control-label required'],
                    'attr' => [
                        'placeholder' => trans('Parent'),
                        'data-counter' => 120,
                    ],
                    'wrapper' => [
                        'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ' col-md-3',
                    ],
                ])
            ->add('closeHtml3','html',[
                'html' => '</div>'
            ])
            ->add('roles_join', 'textarea', [
                'label' => trans('Bộ phận'),
                'attr' => [
                    'rows' => 4,
                    'placeholder' => trans('Vai trò tham gia'),
                    'data-counter' => 400,
                ],
            ])
            ->add('next_step', 'textarea', [
                'label' => trans('Bước tiếp theo'),
                'attr' => [
                    'rows' => 4,
                    'placeholder' => trans('Điều kiện đến bước tiếp theo'),
                    'data-counter' => 400,
                    'class' => 'code-editor form-control',
                    'style' => "font-family: 'Source Code Pro', monospace;"
                ],
            ])
            ->add('main_thread_status', 'customSelect', [
                'label' => trans('core/base::tables.status'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-full',
                ],
                'choices' => [
                    'main_branch' => 'Nhánh chính',
                    'secondary_branch' => 'Nhánh phụ',
                ],
            ])
            ->add('cycle_point', 'customSelect', [
                'label' => trans('Điểm chu kì'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-full',
                ],
                'choices' => [
                    '' => 'Không chọn',
                    'start' => 'Nhánh bắt đầu',
                    'end' => 'Nhánh kết thúc',
                ],
            ])
            ->setBreakFieldPoint('main_thread_status');
    }
}
