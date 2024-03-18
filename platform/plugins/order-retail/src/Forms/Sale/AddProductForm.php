<?php

namespace Botble\OrderRetail\Forms\Sale;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\OrderRetail\Enums\ShippingTypeEnum;
use Botble\OrderRetail\Models\Order;
use Botble\Sales\Enums\TypeOrderEnum;
use Botble\Sales\Http\Requests\SalesRequest;
use Botble\Sales\Models\Sales;

class AddProductForm extends FormAbstract
{
    public function buildForm(): void
    {
        $this
            ->setUrl(route('retail.sale.purchase-order.create.store'))
            ->setupModel(new Order)
            // ->setValidatorClass(SalesRequest::class)
            ->withCustomFields()
            ->add('rowOpen1', 'html', [
                'html' => '<div class="row">',
            ])
            ->add('customer_name', 'text', [
                'label' => "Tên khách hàng",
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => "Tên khách hàng",
                    'data-counter' => 120,
                ],
                'wrapper'    => [
                    'class' => 'form-group col-md-6',
                ],
            ])
            ->add('customer_phone', 'number', [
                'label' => "Số điện thoại",
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => "Số điện thoại",
                    'data-counter' => 120,
                ],
                'wrapper'    => [
                    'class' => 'form-group col-md-6',
                ],
            ])
            ->add('rowClose1', 'html', [
                'html' => '</div>',
            ])
            ->add('field_name', 'datePicker', [
                'label' => "Ngày cần hàng",
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'class' => 'form-control',
                    'data-date-format' => 'd-m-Y',
                ],
            ])
            ->add('rowOpen2', 'html', [
                'html' => '<div class="row">',
            ])
            ->add('address', 'text', [
                'label' => "Địa chỉ nhận hàng",
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => "",
                    'data-counter' => 120,
                ],
                'wrapper'    => [
                    'class' => 'form-group col-md-6',
                ],
            ])
            ->add('shipping_type', 'customSelect', [
                'label' => "Hình thực giao hàng",
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-full',
                ],
                'wrapper'    => [
                    'class' => 'form-group col-md-6',
                ],
                'choices' => ShippingTypeEnum::labels(),
            ])
            ->add('rowClose2', 'html', [
                'html' => '</div>',
            ])
            ->add('note', 'textarea', [
                'label' => "Ghi chú",
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => "",
                    'data-counter' => 500,
                    'rows' => 3,
                ],
            ])
            // Add Products
            ->add('productList', 'html', [
                'html' => view('plugins/order-retail::form.add-product-purchase-field'),
            ])
            ->add('order_type', 'customSelect', [
                'label' => "Loại đơn",
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-full',
                ],
                'wrapper'    => [
                    'class' => 'form-group col-md-6',
                ],
                'choices' => TypeOrderEnum::labels(),
            ])
            
            ->setBreakFieldPoint('order_type');
    }
}
