<?php

namespace Botble\Warehouse\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Warehouse\Http\Requests\TypeMaterialRequest;
use Botble\Warehouse\Models\TypeMaterial;

class TypeMaterialForm extends FormAbstract
{
    public function buildForm(): void
    {
        $list = get_typematerial(['condition' => []]);

        $type_materials = [];
        foreach ($list as $row) {
            if ($this->getModel() && ($this->model->id === $row->id || $this->model->id === $row->parent_id)) {
                continue;
            }

            $type_materials[$row->id] = $row->indent_text . ' ' . $row->name;
        }
        $type_materials = [0 => trans('plugins/warehouse::type_material.none')] + $type_materials;
        $this
            ->setupModel(new TypeMaterial)
            ->setValidatorClass(TypeMaterialRequest::class)
            ->withCustomFields()
            ->add('id', 'hidden', [
                'value' => $this->model->id,
            ])
            ->add('name', 'text', [
                'label' => __('Tên loại'),

                'attr' => [
                    'placeholder' => __('Nhập tên'),
                    'data-counter' => 120,
                ],
            ])
            ->add('description', 'editor', [
                'label' => trans('plugins/warehouse::type_material.desciption'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => trans('core/base::forms.name_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->add('parent_id', 'customSelect', [
                'label' => __('Chọn loại cha'),
                'attr' => [
                    'class' => 'select-search-full',
                ],
                'choices' => $type_materials,
            ])
            ->add('status', 'customSelect', [
                'label' => __('Trạng thái'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-full',
                ],
                'choices' => BaseStatusEnum::labels(),
            ])
            ->setBreakFieldPoint('status');
    }
}
