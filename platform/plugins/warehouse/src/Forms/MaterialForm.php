<?php

namespace Botble\Warehouse\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Warehouse\Enums\BaseUnitEnum;
use Botble\Warehouse\Enums\MaterialStatusEnum;
use Botble\Warehouse\Forms\Fields\MaterialMultiField;
use Botble\Warehouse\Http\Requests\MaterialRequest;
use Botble\Warehouse\Models\Material;
use Botble\Warehouse\Models\TypeMaterial;

class MaterialForm extends FormAbstract
{
    public function buildForm(): void
    {

        $selectedMaterial = [];
        if ($this->getModel()) {
            $selectedMaterial = $this->getModel()->type_materials()->pluck('type_material_id')->all();
        }

        if (!$this->getModel() && empty($selectedMaterial)) {
            $selectedMaterial = TypeMaterial::query()
                ->where('is_default', 1)
                ->pluck('id')
                ->all();
        }

        $this
            ->setupModel(new Material)
            ->setValidatorClass(MaterialRequest::class)
            ->addCustomField('MaterialMulti', MaterialMultiField::class)
            ->withCustomFields()
            ->add('name', 'text', [
                'label' => __('Tên'),
                'attr' => [
                    'placeholder' => __('Nhập tên'),
                    'data-counter' => 120,
                ],
            ])
            ->add('id', 'hidden', [
                'value' => $this->model->id,
            ])
            ->add('code', 'text', [
                'label' => 'Mã nguyên phụ liệu',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => 'Mã nguyên phụ liệu',
                ],
            ])
            ->add('unit', 'text', [
                'label' => 'Đơn vị',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => 'Đơn vị',
                ],
            ])
            ->add('min', 'number', [
                'label' => trans('plugins/warehouse::material.min'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder' => trans('plugins/warehouse::material.min_placeholder'),
                ],
            ])
            ->add('price', 'number', [
                'label' => trans('plugins/warehouse::material.price'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder' => trans('plugins/warehouse::material.price_placeholder'),
                ],
            ])
            ->add('description', 'textarea', [
                'label' => trans('plugins/warehouse::material.description'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'data-counter' => 120,
                ],
            ])
            ->add('status', 'customSelect', [
                'label' => trans('core/base::tables.status'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-full',
                ],
                'choices' => MaterialStatusEnum::labels(),
            ])
            ->add('type_materials[]', 'MaterialMulti', [
                'label' => trans('plugins/warehouse::material.type_material'),
                'label_attr' => ['class' => 'control-label required'],
                'choices' => get_typematerial_with_children(),
                'value' => old('type_materials', $selectedMaterial),
            ])
            ->add('image', 'mediaImage', [
                'label' => trans('plugins/warehouse::material.image'),
                'attr' => [
                    'placeholder' => trans('core/base::forms.name_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->setBreakFieldPoint('status');
    }
}
