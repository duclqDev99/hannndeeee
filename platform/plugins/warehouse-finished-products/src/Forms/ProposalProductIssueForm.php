<?php

namespace Botble\WarehouseFinishedProducts\Forms;

use Botble\Base\Facades\Assets;
use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\WarehouseFinishedProducts\Forms\Fields\ListProductStockField;
use Botble\WarehouseFinishedProducts\Forms\Fields\ProductListBatchField;
use Botble\WarehouseFinishedProducts\Forms\Fields\ProposalProductIssueField;
use Botble\WarehouseFinishedProducts\Http\Requests\ProposalProductIssueRequest;
use Botble\WarehouseFinishedProducts\Models\ProposalProductIssue;
use Botble\WarehouseFinishedProducts\Models\WarehouseFinishedProducts;

class ProposalProductIssueForm extends FormAbstract
{
    public function buildForm(): void
    {

        Assets::addScriptsDirectly([
            'vendor/core/plugins/warehouse-finished-products/js/list-product-stock.js',
            'https://unpkg.com/vue-multiselect@2.1.0',
        ])->addStylesDirectly([
                    'https://unpkg.com/vue-multiselect@2.1.0/dist/vue-multiselect.min.css',
                ])->removeItemDirectly([
                    'vendor/core/core/media/css/media.css'
                ]);
        Assets::usingVueJS();

        $baseQuery = WarehouseFinishedProducts::where('status', BaseStatusEnum::PUBLISHED)
            ->with('warehouseUsers');
        $authUserId = \Auth::id();
        $user = \Auth::user();
        $isAdmin = $user->super_user || $user->hasPermission('warehouse-finished-products.warehouse-all');
        $warehouses = $isAdmin
            ? $baseQuery->get()
            : $baseQuery->whereHas('warehouseUsers', function ($query) use ($authUserId) {
                $query->where('user_id', $authUserId);
            })->get();
        $warehouses = $warehouses->mapWithKeys(function ($warehouse) {
            return [$warehouse->id => $warehouse->name];
        })->toArray();
        $this
            ->setupModel(new ProposalProductIssue)
            ->setValidatorClass(ProposalProductIssueRequest::class)
            ->addCustomField('proposal-product-custom-field', ProposalProductIssueField::class)
            ->addCustomField('listProduct', ListProductStockField::class)
            ->addCustomField('proposal-product-batch-field', ProductListBatchField::class)
            ->withCustomFields()
            ->add('proposal_issue_id', 'hidden', [
                'value' => $this->model->id,
                'attr' => [
                    'id' => 'proposal_issue_id'
                ]
            ])
            ->add('form_title', 'html', [
                'html' => '<h3>Đơn đề xuất xuất kho</h3>'
            ])
            ->add('rowOpen1', 'html', [
                'html' => '<div class="row">'
            ])
            ->add('warehouse_id', 'customSelect', [
                'label' => 'Kho xuất',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-search-full',
                    'id' => 'warehouse_id'
                ],
                'choices' =>  $warehouses,
                'value' => !empty($this->getModel()) ? $this->getModel()->warehouse_id : 0,
                'wrapper' => ['class' => 'col-lg-8 col-12']
            ])
            ->add('general_order_code', 'text', [
                'label' => __('Mã đơn hàng'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nhập mã đơn hàng (nếu có)'
                ],
                'wrapper' => ['class' => 'col-lg-4 col-12']
            ])
            ->add('rowClose1', 'html', [
                'html' => '</div>'
            ])
            ->add('rowAdd1', 'html', [
                'html' => '<div class="my-2"></div>',
            ])
            // ->add('is_batch', 'customRadio', [
            //     'label' => __('Kho nhập thành phẩm'),
            //     'label_attr' => ['class' => 'control-label'],
            //     'choices' => [
            //         '0' => 'Xuất lẻ',
            //         '1' => 'Xuất theo lô',
            //         // '2' => 'Xuất lẻ',
            //     ],
            //     'default_value' => 1
            // ])

            // ->add('is_batch', 'onOffCheckbox', [
            //     'label' => 'Xuất theo lô',
            // ])
            // ->add('is_odd', 'onOffCheckbox', [
            //     'label' => 'Xuất lẻ',
            // ])
            ->add('title', 'text', [
                'label' => __('Mục đích xuất kho'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Mục đích xuất kho'
                ],
            ])
            ->add('is_warehouse', 'customRadio', [
                'label' => __('Kho nhập thành phẩm'),
                'label_attr' => ['class' => 'control-label'],
                'choices' => [
                    '0' => 'Hub',
                    '1' => 'Kho thành phẩm khác',
                    // '2' => 'Xuất lẻ',
                ],
                'default_value' => 0
            ])
            ->add('product_id[]', 'listProduct', [
                'label' => trans('Danh sách thành phẩm'),
                'label_attr' => [
                    'class' => 'control-label required',
                    'id' => 'product',
                ],
                'choices' => ['0' => '151515151'],
            ])
            // ->add('list-branch', 'proposal-product-batch-field')
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
