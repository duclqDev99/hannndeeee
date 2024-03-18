<?php

namespace Botble\OrderRetail\Forms\Sale;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\OrderRetail\Enums\OrderType;
use Botble\OrderRetail\Enums\OrderTypeEnum;
use Botble\OrderRetail\Enums\ShippingTypeEnum;
use Botble\OrderRetail\Models\Order;
use Botble\OrderRetail\Models\OrderQuotation;
use Botble\OrderStepSetting\Enums\ActionEnum;
use Botble\Sales\Enums\TypeOrderEnum;
use Botble\Sales\Http\Requests\SalesRequest;
use Botble\Sales\Models\Sales;

class QuotationForm extends FormAbstract
{
    public function buildForm(): void
    {
        $order = $this->model ? $this->model?->order : null;
        $view = '';

        if($order){
            $order->quotation_amount = $this->model->amount;
            if($order){
                $view = view('plugins/order-retail::form.append-order-info-to-quotation-form', compact('order'));
            }
        }
       
        $this
            ->setUrl(route('retail.sale.quotation.create.store'))
            ->setupModel(new OrderQuotation)
            // ->setValidatorClass(SalesRequest::class)
            ->withCustomFields()
            ->add('rowOpen1', 'html', [
                'html' => '<div class="row">',
            ])
            ->add('title', 'text', [
                'label' => "Tiêu đề",
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => "Nhập tiêu đề",
                    'data-counter' => 120,
                ],
            ])
            ->add('rowClose1', 'html', [
                'html' => '</div>',
            ])

            ->add('rowOpen2', 'html', [
                'html' => '<div class="row">',
            ])
            ->add('start_date', 'datePicker', [
                'label' => "Ngày hiệu lực",
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'class' => 'form-control',
                ],
                'wrapper'    => [
                    'class' => 'form-group col-md-6',
                ],
            ])
            ->add('due_date', 'datePicker', [
                'label' => "Hạn thanh toán",
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'class' => 'form-control',
                ],
                'wrapper'    => [
                    'class' => 'form-group col-md-6',
                ],
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
            ->add('search_type', 'hidden', ['value' => 'quotation'])
            ->add('order_code', 'text', [
                'label' => "Số YCSX",
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => "Tìm kiếm YCSX",
                    'data-counter' => 120,
                ],
            ])
            ->add('order_info', 'html', [
                'html' => '<div class="order-pick-info">
                   '.$view.'
                </div>',
            ]);
    }
}
