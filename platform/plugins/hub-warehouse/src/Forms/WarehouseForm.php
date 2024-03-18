<?php

namespace Botble\HubWarehouse\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\HubWarehouse\Http\Requests\WarehouseRequest;
use Botble\HubWarehouse\Models\HubWarehouse;
use Botble\HubWarehouse\Models\Warehouse;
use Botble\WarehouseFinishedProducts\Enums\HubStatusEnum;

class WarehouseForm extends FormAbstract
{
    public function buildForm(): void
    {
        $hubData = HubWarehouse::with('hubUsers')->whereHas('hubUsers', function ($query) {
            $authUserId = \Auth::id();
            $query->where('user_id', $authUserId);
        })->get();
        if (\Auth::user()->super_user) {
            $hub = HubWarehouse::query()->pluck('name', 'id')->all();
        } else {
            $hub = $hubData->pluck('name', 'id')->all();
        }
        $this
            ->setupModel(new Warehouse)
            ->setValidatorClass(WarehouseRequest::class)
            ->withCustomFields()
            ->add('hub_id', 'customSelect', [
                'label' => 'HUB',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-full',
                ],
                'choices' =>  $hub,
            ])
            ->add('name', 'text', [
                'label' => trans('Tên kho Hub'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => trans('Tên kho Hub'),
                    'data-counter' => 120,
                ],
            ])
            ->add('is_watse', 'customRadio', [
                'label' => __('Loại kho'),
                'label_attr' => ['class' => 'control-label'],
                'choices' => [
                    '0' => 'Kho thường',
                    '1' => 'Kho phế phẩm',
                ],
                'default_value' => 0
            ])
            ->add('status', 'customSelect', [
                'label' => trans('core/base::tables.status'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-full',
                ],
                'choices' => HubStatusEnum::labels(),
            ])

            ->setBreakFieldPoint('status');
    }
}
