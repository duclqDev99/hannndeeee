<?php

namespace Botble\Showroom\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\HubWarehouse\Models\HubWarehouse;
use Botble\Showroom\Http\Requests\ShowroomRequest;
use Botble\Showroom\Models\Showroom;
use Botble\WarehouseFinishedProducts\Enums\HubStatusEnum;

class ShowroomForm extends FormAbstract
{
    public function buildForm(): void
    {

        $title_name = trans('plugins/showroom::showroom.column_name_table');
        $this
            ->setupModel(new Showroom)
            ->setValidatorClass(ShowroomRequest::class)
            ->withCustomFields()
            ->add('id', 'hidden', [
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'value' => $this?->id
                ],
            ])
            ->add('hub_id', 'customSelect', [
                'label' => "Chọn HUB",
                'attr' => [
                    'class' => 'select-search-full',
                ],
                'choices' => HubWarehouse::where('status', HubStatusEnum::ACTIVE)->pluck('name', 'id')->toArray(),
            ])
            ->add('name', 'text', [
                'label' => $title_name['name'],
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => $title_name['name'],
                    'data-counter' => 120,
                ],
            ])
            ->add('code', 'text', [
                'label' => "Mã showroom",
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => "Nhập mã showroom",
                    'data-counter' => 120,
                ],
            ])
            ->add('phone_number', 'number', [
                'label' => $title_name['phone_number'],
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => $title_name['phone_number'],
                    'data-counter' => 120,
                ],
            ])
            ->add('address', 'text', [
                'label' => $title_name['address'],
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder' => $title_name['address'],
                    'data-counter' => 120,
                ],
            ])
            ->add('description', 'textarea', [
                'label' => __($title_name['description']),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder' => __($title_name['description']),
                ],
            ])
            ->add('status', 'customSelect', [
                'label' => $title_name['status'],
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-full',
                ],
                'choices' => BaseStatusEnum::labels(),
            ])
            ->setBreakFieldPoint('status');
    }
}
