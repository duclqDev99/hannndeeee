<?php

namespace Botble\SaleWarehouse\Forms;

use Botble\Base\Facades\Assets;
use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\HubWarehouse\Models\HubWarehouse;
use Botble\SaleWarehouse\Enums\SaleWarehouseStatusEnum;
use Botble\SaleWarehouse\Forms\Fields\SaleProposalIssueField;
use Botble\SaleWarehouse\Http\Requests\SaleProposalIssueRequest;
use Botble\SaleWarehouse\Models\SaleProposalIssue;
use Botble\SaleWarehouse\Models\SaleWarehouse;

class SaleProposalIssueForm extends FormAbstract
{
    public function buildForm(): void
    {
        Assets::addScriptsDirectly([
            'vendor/core/plugins/sale-warehouse/js/sale-proposal-issue.js',
            'https://unpkg.com/vue-multiselect@2.1.0',
        ])->addStylesDirectly([
                    'https://unpkg.com/vue-multiselect@2.1.0/dist/vue-multiselect.min.css',
                ])->removeItemDirectly([
                    'vendor/core/core/media/css/media.css'
                ]);
        Assets::usingVueJS();
        $saleData = SaleWarehouse::with('saleUsers')->whereHas('saleUsers', function ($query) {
            $authUserId = \Auth::id();
            $query->where('user_id', $authUserId);
        })->where('status', SaleWarehouseStatusEnum::ACTIVE)->get();

        if (\Auth::user()->hasPermission('sale-warehouse.all')) {
            $sale = SaleWarehouse::query()->where('status', SaleWarehouseStatusEnum::ACTIVE)->pluck('name', 'id')->all();
        } else {
            $sale = $saleData->pluck('name', 'id')->all();
        }
        $this
            ->setupModel(new SaleProposalIssue)
            ->setValidatorClass(SaleProposalIssueRequest::class)
            ->addCustomField('sale-proposal-issue-custom-field', SaleProposalIssueField::class)
            ->withCustomFields()
            ->add('form_title', 'html', [
                'html' => '<h3>Đơn đề xuất xuất kho</h3>'
            ])
            ->add('proposal_id', 'hidden', [
                'value' => $this->model->id,
                'attr' => [
                    'id' => 'proposal_id'
                ]
            ])
            ->add('sale_id', 'customSelect', [
                'label' => 'Kho SALE',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-search-full',
                    'id' => 'sale_id',

                ],
                'choices' => count($sale) > 0 ?  $sale : ['0' => 'Không có sale'],
            ])
            ->add('rowOpen1', 'html', [
                'html' => '<div class="row">'
            ])
            ->add('warehouse_issue_id', 'customSelect', [
                'label' => 'Kho'    ,
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-search-full',
                    'id' => 'warehouse_issue_id',

                ],
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
            ->add('title', 'text', [
                'label' => 'Mục đích xuất kho',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => 'Mục đích xuất kho',
                    'data-counter' => 120,
                    'id' => 'title'
                ]

            ])
            ->add('rowClose1', 'html', [
                'html' => '</div>'
            ])
            ->add('is_warehouse', 'customRadio', [
                'label' => __('Xuất thành phẩm đến'),
                'label_attr' => ['class' => 'control-label'],
                'choices' => [
                    'tour' => 'Xuất đi giải',
                ],
                'default_value' => 'tour'
            ])
            ->add('nav', 'sale-proposal-issue-custom-field')

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
