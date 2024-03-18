<?php

namespace Botble\Sales\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Sales\Http\Requests\CustomerRequest;
use Botble\Sales\Models\Customer;

class CustomerForm extends FormAbstract
{
    public function buildForm(): void
    {
        $this
            ->setupModel(new Customer())
            ->setValidatorClass(CustomerRequest::class)
            ->withCustomFields()
            ->add('rowOpen1', 'html', [
                'html' => '<div class="row">'
            ])
            ->add('first_name', 'text', [
                'label' => 'Tên khách hàng',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => 'Nhập tên khách hàng',
                    'data-counter' => 50,
                ],
                'wrapper' => ['class' => 'col-lg-4 col-md-6 col-12']
            ])
            ->add('last_name', 'text', [
                'label' => 'Họ và tên đệm',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => 'Nhập họ',
                    'data-counter' => 50,
                ],
                'wrapper' => ['class' => 'col-lg-4 col-md-6 col-12']
            ])
            ->add('gender', 'customSelect', [
                'label' => 'Giới tính',
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'class' => 'form-control select-full',
                ],
                'choices' => [
                    'Nam' => 'Nam',
                    'Nữ' => 'Nữ',
                    'Khác' => 'Khác'
                ],
                'wrapper' => ['class' => 'col-lg-4 col-md-6 col-12']
            ])
            ->add('email', 'email', [
                'label' => 'Email',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => 'example@gmail.com',
                    'data-counter' => 255,
                ],
                'wrapper' => ['class' => 'col-lg-4 col-md-6 col-12']
            ])
            ->add('phone', 'text', [
                'label' => 'Số điện thoại',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => '+84',
                    'data-counter' => 25,
                ],
                'wrapper' => ['class' => 'col-lg-4 col-md-6 col-12']
            ])
            ->add('dob', 'date', [
                'label' => 'Ngày sinh',
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                ],
                'wrapper' => ['class' => 'col-lg-4 col-md-6 col-12']
            ])
            ->add('address', 'textarea', [
                'label' => 'Địa chỉ',
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'rows' => 3
                ],
                'wrapper' => ['class' => 'col-lg-8 col-md-6 col-12 mt-3']
            ])
            ->add('rowClose1', 'html', [
                'html' => '</div>'
            ])
            ->add('level', 'customSelect', [
                'label' => 'Kiểu khách hàng',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-full',
                ],
                'choices' => [
                    'normal' => 'Khách hàng thường',
                    'special' => 'Khách hàng đặc biệt',
                    'vip' => 'Khách hàng VIP',
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
            ->setBreakFieldPoint('level');
    }
}
