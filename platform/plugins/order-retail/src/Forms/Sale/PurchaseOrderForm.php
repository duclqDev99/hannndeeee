<?php

namespace Botble\OrderRetail\Forms\Sale;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\OrderRetail\Enums\OrderType;
use Botble\OrderRetail\Enums\OrderTypeEnum;
use Botble\OrderRetail\Enums\ShippingTypeEnum;
use Botble\OrderRetail\Http\Requests\PurchaseOrderRequest;
use Botble\OrderRetail\Models\Order;
use Botble\Sales\Enums\TypeOrderEnum;
use Botble\Sales\Http\Requests\SalesRequest;
use Botble\Sales\Models\Sales;

class PurchaseOrderForm extends FormAbstract
{
    public function buildForm(): void
    {
        $this
            ->setUrl(route('retail.sale.purchase-order.create.store'))
            ->setupModel(new Order)
            ->setValidatorClass(PurchaseOrderRequest::class)
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
                    'class' => 'form-group col-md-8',
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
                    'class' => 'form-group col-md-4',
                ],
            ])
            ->add('expected_date', 'datePicker', [
                'label' => "Ngày cần hàng",
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'class' => 'form-control',
                    // 'data-date-format' => 'd-m-Y',
                ],
                'wrapper'    => [
                    'class' => 'form-group col-md-8',
                ],
            ])
            ->add('order_type', 'customSelect', [
                'label' => "Loại đơn",
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-full',
                ],
                'wrapper'    => [
                    'class' => 'form-group col-md-4',
                ],
                'choices' => OrderTypeEnum::labels(),
            ])
            ->add('rowClose1', 'html', [
                'html' => '</div>',
            ])  
           
            ->add('rowOpen2', 'html', [
                'html' => '<div class="row">',
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
            ->add('product-box', 'html', [
                'html' => '<div class="card product-card">
                    <div class="card-header">
                        <div class="w-100 d-flex align-items-center justify-content-between">
                            <label class="control-label mb-0 uppercase">Thông tin mặt hàng</label>
                            <div class="card">
                                <button class="btn" type="button" data-bs-toggle="modal" data-bs-target="#add-product-modal">
                                    <span class="me-2">
                                        <i class="fa-solid fa-plus"></i>
                                    </span>
                                    Thêm
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body" style="display:none;">
                        <div id="product-files" class="d-none"></div>
                        <table class="table table-bordered table-vcenter">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Mã sản phẩm</th>
                                    <th>Tên thành phẩm</th>
                                    <th width="90">Số lượng</th>
                                    <th>Đơn vị tính</th>
                                    <th>Giá dự kiến</th>
                                    <th>Thành tiền</th>
                                    <th>Thiết kế</th>
                                    <th>Tùy chọn</th>
                                </tr>
                            </thead>
                            <tbody id="product-preview">
                                
                            </tbody>
                        </table>
                    </div>
                </div>',
            ]);
    }
}
