<?php

namespace Botble\Showroom\Forms;

use Botble\Agent\Forms\Fields\ProposalAgentIssueField;
use Botble\Agent\Models\Agent;
use Botble\Base\Facades\Assets;
use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Agent\Http\Requests\AngentProposalIssueRequest;
use Botble\Agent\Models\AngentProposalIssue;
use Botble\Showroom\Forms\Fields\ShowroomProposalIssueField;
use Botble\Showroom\Models\Showroom;
use Botble\Showroom\Models\ShowroomProposalIssue;

class ShowroomProposalIssueForm extends FormAbstract
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
            'vendor/core/plugins/showroom/js/proposal-showroom-issue.js',
            'https://unpkg.com/vue-multiselect@2.1.0',
        ])->addStylesDirectly([
            'https://unpkg.com/vue-multiselect@2.1.0/dist/vue-multiselect.min.css',
        ])->removeItemDirectly([
            'vendor/core/core/media/css/media.css'
        ]);
        Assets::usingVueJS();
        // $showrooms = Showroom::pluck('name', 'id')->all();
        $this
            ->setupModel(new ShowroomProposalIssue)
            ->addCustomField('proposal-showroom-issue-custom-field', ShowroomProposalIssueField::class)
            ->withCustomFields()
            ->add('proposal_id', 'hidden', [
                'value' => $this->model->id,
                'attr' => [
                    'id' => 'proposal_id'
                ]
            ])
            ->add('form_title', 'html', [
                'html' => '<h3>Đơn yêu cầu trả hàng</h3>'
            ])
            ->add('showroom_id', 'customSelect', [
                'label' => 'Showroom',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-search-full',
                    'id' => 'showroom_id'
                ],
                'choices' => $showrooms,
            ])
            ->add('rowOpen1', 'html', [
                'html' => '<div class="row">'
            ])

            ->add('warehouse_issue_id', 'customSelect', [
                'label' => 'Kho',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-search-full',
                    'id' => 'warehouse_issue_id'
                ],
                'choices' => ['0' => 'Vui lòng chọn showroom trước'],
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
                'label' => 'Lý do trả hàng',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => 'Lý do trả hàng',
                    'data-counter' => 120,
                    'id' => 'title'
                ],
            ])
            ->add('nav', 'proposal-showroom-issue-custom-field')
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
