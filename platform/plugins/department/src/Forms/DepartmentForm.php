<?php

namespace Botble\Department\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Department\Http\Requests\DepartmentRequest;
use Botble\Department\Models\Department;

class DepartmentForm extends FormAbstract
{
    public function buildForm(): void
    {
        $this
            ->setupModel(new Department)
            ->setValidatorClass(DepartmentRequest::class)
            ->withCustomFields()
            ->add('id', 'hidden', [])
            ->add('name', 'text', [
                'label' => 'Tên bộ phận',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => trans('core/base::forms.name_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->add('code', 'text', [
                'label' => 'Mã bộ phận',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => 'Nhập mã',
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
