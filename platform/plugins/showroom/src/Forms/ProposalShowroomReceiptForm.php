<?php

namespace Botble\Showroom\Forms;

use Botble\Agent\Forms\Fields\ProposalAgentReceiptField;
use Botble\Agent\Models\Agent;
use Botble\Agent\Models\AgentWarehouse;
use Botble\Base\Facades\Assets;
use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Agent\Http\Requests\ProposalAgentReceiptRequest;
use Botble\Agent\Models\ProposalAgentReceipt;
use Botble\Showroom\Forms\Fields\ProposalShowroomReceiptField;
use Botble\Showroom\Models\ProposalShowroomReceipt;
use Botble\Showroom\Models\Showroom;
use Botble\Showroom\Models\ShowroomProposalReceipt;

class ProposalShowroomReceiptForm extends FormAbstract
{

    public function buildForm(): void
    {

        $showroomData = Showroom::whereHas('users', function ($query) {
            $authUserId = \Auth::id();
            $query->where('user_id', $authUserId);
        })->where('status', BaseStatusEnum::PUBLISHED)->get();

        if (\Auth::user()->hasPermission('showroom.all')) {
            $showrooms = Showroom::query()
                ->where('status', BaseStatusEnum::PUBLISHED)
                ->pluck('name', 'id')
                ->all();
        } else {
            $showrooms = $showroomData->pluck('name', 'id')->all();
        }
        Assets::addScriptsDirectly([
            'vendor/core/plugins/showroom/js/proposal-showroom-receipt.js',
            'https://unpkg.com/vue-multiselect@2.1.0',
        ])->addStylesDirectly([
                    'https://unpkg.com/vue-multiselect@2.1.0/dist/vue-multiselect.min.css',
                ])->removeItemDirectly([
                    'vendor/core/core/media/css/media.css'
                ]);
        Assets::usingVueJS();
        $this
            ->setupModel(new ShowroomProposalReceipt())
            ->addCustomField('proposal-showroom-receipt-custom-field', ProposalShowroomReceiptField::class)
            ->withCustomFields()
            ->add('proposal_id', 'hidden', [
                'value' => $this->model->id,
                'attr' => [
                    'id' => 'proposal_id'
                ]
            ])
            ->add('showroom_id', 'customSelect', [
                'label' => 'Showroom',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-search-full',
                    'id' => 'showroom_id',

                ],
                'choices' =>  $showrooms,
            ])
            ->add('rowOpen1', 'html', [
                'html' => '<div class="row">'
            ])
            ->add('warehouse_receipt_id', 'customSelect', [
                'label' => 'Kho',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-search-full',
                    'id' => 'warehouse_receipt_id',
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
            ->add('rowClose1', 'html', [
                'html' => '</div><br>'
            ])
            ->add('title', 'text', [
                'label' => 'Mục đích nhập kho',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => 'Mục đích nhập kho',
                    'data-counter' => 120,
                    'id' => 'title'
                ],
            ])
            ->add('nav', 'proposal-showroom-receipt-custom-field')
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
