<?php

namespace Botble\Showroom\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Showroom\Enums\ShowroomStatusEnum;
use Botble\Showroom\Http\Requests\ShowroomWarehouseRequest;
use Botble\Showroom\Models\ShowroomWarehouse;

class ShowroomWarehouseForm extends FormAbstract
{
    public function buildForm(): void
    {
        $listShowroom = get_showroom_for_user()->pluck('name', 'id');
        $title_name = trans('plugins/showroom::showroom.column_name_table');
        $this
            ->setupModel(new ShowroomWarehouse())
            ->setValidatorClass(ShowroomWarehouseRequest::class)
            ->withCustomFields()
            ->add('showroom_id','customSelect',[
                'label' =>"Chọn " . $title_name['showroom'] ,
                'attr' => [
                    'class' => 'select-search-full',
                ],
                'choices' => $listShowroom->toArray(),
            ])
            ->add('name', 'text', [
                'label' => $title_name['name'] ,
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => $title_name['name'] ,
                    'data-counter' => 120,
                ],
            ])
            ->add('address', 'text', [
                'label' => $title_name['address'],
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => $title_name['address'],
                    'data-counter' => 120,
                ],
            ])
            ->add('description', 'textarea', [
                'label' =>  __($title_name['description']),
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
                'choices' => ShowroomStatusEnum::labels(),
            ])
            ->setBreakFieldPoint('status');
    }
}
