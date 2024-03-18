<?php

namespace Botble\Department\Forms;

use Botble\ACL\Models\Role;
use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Department\Http\Requests\DepartmentUserRequest;
use Botble\Department\Models\Department;
use Botble\Department\Models\DepartmentUser;

class DepartmentUserForm extends FormAbstract
{
    public function buildForm(): void
    {
        $this
            ->setupModel(new DepartmentUser)
            ->setValidatorClass(DepartmentUserRequest::class)
            ->withCustomFields()
            ->add('id', 'hidden', [])
            ->add('rowOpen1', 'html', [
                'html' => '<div class="row">',
            ])
            ->add('first_name', 'text', [
                'label' => __('Họ/Tên đệm'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => __(''),
                    'data-counter' => 60,
                ],
                'wrapper'    => [
                    'class' => 'form-group col-md-6',
                ],
            ])
            ->add('last_name', 'text', [
                'label' => __('Tên'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => __(''),
                    'data-counter' => 60,
                ],
                'wrapper'    => [
                    'class' => 'form-group col-md-6',
                ],
            ])
            ->add('rowClose1', 'html', [
                'html' => '</div>',
            ])

            ->add('email', 'email', [
                'label' => __('Email'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => __(''),
                    'data-counter' => 60,
                ],
            ])
            ->add('phone', 'number', [
                'label' => __('Số điện thoại'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => __(''),
                    'data-counter' => 120,
                ],
            ]);


        if (request()->route()->getName() == 'department-user.create') {
            $this
                ->add('username', 'text', [
                    'label' => __('Tên tài khoản'),
                    'label_attr' => ['class' => 'control-label required'],
                    'attr' => [
                        'placeholder' => __(''),
                        'data-counter' => 30,
                    ],
                ])
                ->add('rowOpen2', 'html', [
                    'html' => '<div class="row">',
                ])
                ->add('password', 'password', [
                    'label' => __('Mật khẩu'),
                    'label_attr' => ['class' => 'control-label required'],
                    'attr' => [
                        'placeholder' => __(''),
                        'data-counter' => 60,
                    ],
                    'wrapper'    => [
                        'class' => 'form-group col-md-6',
                    ],
                ])
                ->add('password_confirmation', 'password', [
                    'label' => __('Xác nhận mật khẩu'),
                    'label_attr' => ['class' => 'control-label required'],
                    'attr' => [
                        'placeholder' => __(''),
                        'data-counter' => 60,
                    ],
                    'wrapper'    => [
                        'class' => 'form-group col-md-6',
                    ],
                ])
                ->add('rowClose2', 'html', [
                    'html' => '</div>',
                ]);
        }

        $this->add('department_code', 'customSelect', [
            'label' => __('Bộ phận'),
            'label_attr' => ['class' => 'control-label required'],
            'attr' => [
                'class' => 'form-control select-full',
            ],
            'choices' => ['' => 'Chọn bộ phận'] + get_departments()->pluck('name', 'code')->toArray(),
        ])
            ->add('role_id', 'customSelect', [
                'label' => __('Vai trò'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-full',
                ],
                'choices' => ['' => 'Chọn vai trò'] + Role::pluck('name', 'id')->toArray(),
                'selected' => $this->model->roles->first()->id ?? ''
            ])
            ->setBreakFieldPoint('department_code');
    }
}
