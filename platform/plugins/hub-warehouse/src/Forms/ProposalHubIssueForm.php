<?php

namespace Botble\HubWarehouse\Forms;

use Botble\Base\Facades\Assets;
use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\HubWarehouse\Forms\Fields\ProposalHubIssueField;
use Botble\HubWarehouse\Http\Requests\ProposalHubIssueRequest;
use Botble\HubWarehouse\Models\HubWarehouse;
use Botble\HubWarehouse\Models\ProposalHubIssue;
use Botble\WarehouseFinishedProducts\Enums\HubStatusEnum;

class ProposalHubIssueForm extends FormAbstract
{

    public function buildForm(): void
    {
        Assets::addScriptsDirectly([
            'vendor/core/plugins/hub-warehouse/js/proposal-hub-issue-form.js',
            'https://unpkg.com/vue-multiselect@2.1.0',
        ])->addStylesDirectly([
                    'https://unpkg.com/vue-multiselect@2.1.0/dist/vue-multiselect.min.css',
                ])->removeItemDirectly([
                    'vendor/core/core/media/css/media.css'
                ]);
        Assets::usingVueJS();
        $hubData = HubWarehouse::with('hubUsers')->whereHas('hubUsers', function ($query) {
            $authUserId = \Auth::id();
            $query->where('user_id', $authUserId);
        })->where('status', HubStatusEnum::ACTIVE)->get();

        if (\Auth::user()->hasPermission('hub-warehouse.all-permissions')) {
            $hub = HubWarehouse::query()->where('status', HubStatusEnum::ACTIVE)->pluck('name', 'id')->all();
        } else {
            $hub = $hubData->pluck('name', 'id')->all();
        }
        $this
            ->setupModel(new ProposalHubIssue)
            ->setValidatorClass(ProposalHubIssueRequest::class)
            ->addCustomField('proposal-hub-receipt-custom-field', ProposalHubIssueField::class)
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
            ->add('hub_id', 'customSelect', [
                'label' => 'HUB',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-search-full',
                    'id' => 'hub_id',

                ],
                'choices' => count($hub) > 0 ?  $hub : ['0' => 'Không có hub'],
            ])
            ->add('rowOpen1', 'html', [
                'html' => '<div class="row">'
            ])
            ->add('warehouse_issue_id', 'customSelect', [
                'label' => 'Kho',
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
                    '0' => 'Đại lý',
                    '4' => 'Showroom',
                    '5' => 'Kho sale',
                    // '1' => 'Hub khác',
                    '6' => 'Xuất đi giải',
                    // '2' => 'Kho khác trong hub',
                    // '3' => 'Kho thành phẩm',
                ],
                'default_value' => 0
            ])
            ->add('nav', 'proposal-hub-receipt-custom-field')
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
