<?php

namespace Botble\OrderRetail\Forms\Sale;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\OrderRetail\Enums\OrderType;
use Botble\OrderRetail\Enums\OrderTypeEnum;
use Botble\OrderRetail\Enums\ShippingTypeEnum;
use Botble\OrderRetail\Models\Order;
use Botble\OrderRetail\Models\OrderProduction;
use Botble\OrderRetail\Models\OrderQuotation;
use Botble\OrderStepSetting\Enums\ActionEnum;
use Botble\Sales\Enums\TypeOrderEnum;
use Botble\Sales\Http\Requests\SalesRequest;
use Botble\Sales\Models\Sales;

class ProductionForm extends FormAbstract
{
    public function buildForm(): void
    {
        $order = $this->model ? $this->model->order : null;
        $view = '';
        
        if($order){
            $view = view('plugins/order-retail::form.append-order-info-to-production-form', compact('order'));
        }

        $this
            ->setUrl(route('retail.sale.production.create.store'))
            ->setupModel(new OrderProduction)
            // ->setValidatorClass(SalesRequest::class)
            ->withCustomFields()
            
            ->add('note', 'textarea', [
                'label' => "Ghi chú",
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => "",
                    'data-counter' => 500,
                    'rows' => 3,
                ],
            ])
            ->add('search_type', 'hidden', ['value' => 'production'])
            ->add('order_code', 'text', [
                'label' => "Số YCSX",
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => "Tìm kiếm YCSX",
                    'data-counter' => 120,
                ],
            ])
            ->add('order_info', 'html', [
                'html' => '<div class="order-pick-info">'.$view.'</div>',
            ]);
    }
}
