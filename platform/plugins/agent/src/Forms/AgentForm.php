<?php

namespace Botble\Agent\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Agent\Http\Requests\AgentRequest;
use Botble\Agent\Models\Agent;
use Botble\HubWarehouse\Models\HubWarehouse;
use Botble\WarehouseFinishedProducts\Enums\HubStatusEnum;

class AgentForm extends FormAbstract
{
    public function buildForm(): void
    {
        $discount_value = !empty($this->model->discount_value) ? $this?->model->discount_value : 0;
        $discount_type = !empty($this->model->discount_type) ? $this?->model->discount_type : '%';
        $this
            ->setupModel(new Agent)
            ->setValidatorClass(AgentRequest::class)
            ->withCustomFields()
            ->add('hub_id', 'customSelect', [
                'label' => "Chọn HUB",
                'attr' => [
                    'class' => 'select-search-full',
                ],
                'choices' => HubWarehouse::where('status', HubStatusEnum::ACTIVE)->pluck('name', 'id')->toArray(),
            ])
            ->add('name', 'text', [
                'label' => trans('core/base::forms.name'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => trans('core/base::forms.name_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->add('phone_number', 'number', [
                'label' => 'Số điện thoại',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => 'Số điện thoại',
                    'data-counter' => 120,
                ],
            ])
            ->add('address', 'text', [
                'label' => 'Địa chỉ',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => 'Đia chỉ',
                    'data-counter' => 120,
                ],
            ])
            ->add('description', 'textarea', [
                'label' => __('Mô tả'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder' => __('Mô tả'),
                ],
            ])

            ->add('status', 'customSelect', [
                'label' => trans('core/base::tables.status'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-full',
                ],
                'choices' => BaseStatusEnum::labels(),
            ])
            ->add('discount_value', 'html', [
                'label' => trans('Chiết khấu'),
                'html' => '
                    <div class="next-form-grid"><div class="mb-3 position-relative"><div class="row">
                        <div class="col-auto">
                            <span class="btn btn-active ' . ($discount_type == 'VNĐ' ? "active" : "") . '" id="btn-active-discount-amount" value="amount"> VNĐ</span>&nbsp;
                            <span class="btn btn-active ' . ($discount_type == 'VNĐ' ? "" : "active") . '" id="btn-active-discount" value="percentage"> %</span>
                        </div>
                        <div class="col">
                            <div class="input-group input-group-flat">
                                <input pattern="([0-9]{1,3}).([0-9]{1,3})" value="' . $discount_value .'" name="discount_value" type="number"  class="form-control"/>
                                <input name="discount_type" id="discount_type_input" value="'. $discount_type .'" class="form-control hidden"/>
                                <span id="input-group-text" class="input-group-text">' . ($discount_type == 'VNĐ' ? "VNĐ" : "%") . '</span>
                            </div>
                        </div>
                    </div>
                ',
            ])
            // ->add('discount_type', 'customSelect', [
            //     'label' => 'Chọn loại chiết khấu',
            //     'label_attr' => ['class' => 'control-label required'],
            //     'attr' => [
            //         'class' => 'form-control select-full',
            //     ],
            //     'choices' => [
            //         '%' => '%',
            //         'amount' => 'amount',
            //     ],
            //     'wrapper'    => [
            //         'class' => 'form-group col-md-3',
            //     ],
            // ])
            // ->add('discount_value', 'number', [
            //     'label' => 'Giá trị chiết khấu',
            //     'label_attr' => ['class' => 'control-label required'],
            //     'attr' => [
            //         'placeholder' => 'Giá trị chiết khấu',
            //         'data-counter' => 120,
            //     ],
            //     'wrapper'    => [
            //         'class' => 'form-group col-md-3',
            //     ],
            // ])
            // ->add('row_1_close', 'html', [
            //     'html' => '</div>',
            // ])
            ->setBreakFieldPoint('status');
    }
}
