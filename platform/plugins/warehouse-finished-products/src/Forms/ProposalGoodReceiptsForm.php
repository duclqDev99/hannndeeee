<?php

namespace Botble\WarehouseFinishedProducts\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\Assets;
use Botble\Warehouse\Http\Requests\MaterialProposalReceiptRequest;
use Botble\WarehouseFinishedProducts\Forms\Fields\ProposalProductField;
use Botble\WarehouseFinishedProducts\Forms\Fields\ProposalProductReceiptField;
use Botble\WarehouseFinishedProducts\Models\ProposalReceiptProducts;
use Botble\WarehouseFinishedProducts\Models\WarehouseFinishedProducts;

class ProposalGoodReceiptsForm extends FormAbstract
{
    public function buildForm(): void
    {
        Assets::addScriptsDirectly([
            'vendor/core/plugins/warehouse-finished-products/js/proposal-product-receipt.js',
            'https://unpkg.com/vue-multiselect@2.1.0',
        ])->addStylesDirectly([
            'https://unpkg.com/vue-multiselect@2.1.0/dist/vue-multiselect.min.css',
        ])->removeItemDirectly([
            'vendor/core/core/media/css/media.css'
        ]);
        Assets::usingVueJS();
        $selectWarehouse = [];
        $user = \Auth::user();
        $authUserId = \Auth::id();
        $warehouse = WarehouseFinishedProducts::where(['status' => BaseStatusEnum::PUBLISHED])->with('warehouseUsers');
        $isAdmin = $user->super_user || $user->hasPermission('warehouse-finished-products.warehouse-all');
        $warehouse = $isAdmin
            ? $warehouse->get()
            : $warehouse->whereHas('warehouseUsers', function ($query) use ($authUserId) {
                $query->where('user_id', $authUserId);
            })->get();

        foreach ($warehouse as $item) {
            $selectWarehouse[$item->id] = $item->name;
        }
        $URL_API = env('APP_URL') . '/api/v1/';
        $API_KEY = env('API_KEY');

        $this
            ->setupModel(new ProposalReceiptProducts())
            ->setValidatorClass(MaterialProposalReceiptRequest::class)
            // ->addCustomField('proposal-product-custom-field', ProposalProductField::class)
            ->addCustomField('proposal-product-receipt-custom-field', ProposalProductReceiptField::class)
            ->withCustomFields()
            ->add('url_api', 'text', [
                'label_show' => false,
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'url_api'
                ],
                'value' => $URL_API,
                'wrapper' => ['class' => 'd-none']
            ])
            ->add('api_key', 'text', [
                'label_show' => false,
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'api_key'
                ],
                'value' => $API_KEY,
                'wrapper' => ['class' => 'd-none']
            ])
            ->add('proposal_id', 'number', [
                'label_show' => false,
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'proposal_id',
                    'data-stock' => !empty($this->getModel()) ? $this->getModel()->is_warehouse : '',
                ],
                'value' => !empty($this->getModel()) ? $this->getModel()->id : 0,
                'wrapper' => ['class' => 'd-none']
            ])
            ->add('rowOpen1', 'html', [
                'html' => '<div class="row">'
            ])
            ->add('warehouse_id', 'customSelect', [
                'label' => 'Kho hàng nhập',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-search-full',
                    'id' => 'warehouse_id'
                ],
                'choices' => $selectWarehouse,
                'value' => !empty($this->getModel()) ? $this->getModel()->warehouse_id : 0,
                'wrapper' => ['class' => 'col-lg-8 col-12']
            ])
            ->add('general_order_code', 'text', [
                'label' => __('Mã đơn hàng'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nhập mã đơn hàng (nếu có)',
                    'id' => 'general_order_code'
                ],
                'wrapper' => ['class' => 'col-lg-4 col-12']
            ])
            ->add('rowClose1', 'html', [
                'html' => '</div>'
            ])
            ->add('title', 'text', [
                'label' => __('Mục đích nhập kho'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nhập mục đích',
                ],
            ])
            // ->add('nav', 'proposal-product-custom-field')
            ->add('is_warehouse', 'customRadio', [
                'label' => __('Nhập thành phẩm từ'),
                'label_attr' => ['class' => 'control-label'],
                'choices' => [
                    'stock-odd' => 'Kho thành phẩm khác',
                    'inventory' => 'Hàng lẻ',
                ],
                'default_value' => 'stock-odd'
            ])
            ->add(
                'nav-1',
                'proposal-product-receipt-custom-field',
            )
            ->add('expected_date', 'datePicker', [
                'label' => trans('Ngày dự kiến'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'class' => 'form-control ',
                ],
                'default_value' => date('Y-m-d'),
            ])
            ->add('description', 'textarea', [
                'label' => trans('Ghi chú'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ghi chú',
                    'rows' => 3
                ],
            ])
            ->setBreakFieldPoint('expected_date');
    }
}
