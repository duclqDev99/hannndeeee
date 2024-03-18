<?php

namespace Botble\Warehouse\Forms;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Forms\FormAbstract;
use Botble\Warehouse\Http\Requests\CategoryRequest;
use Botble\Warehouse\Models\Category;

class CategoryForm extends FormAbstract
{
    public function buildForm(): void
    {
        $list = get_warehouse_categories(['condition' => []]);

        $categories = [];
        foreach ($list as $row) {
            if ($this->getModel() && ($this->model->id === $row->id || $this->model->id === $row->parent_id)) {
                continue;
            }

            $categories[$row->id] = $row->indent_text . ' ' . $row->name;
        }
        $categories = [0 => trans('plugins/warehouse::warehouse.categories.none')] + $categories;

        $this
            ->setupModel(new Category())
            ->setValidatorClass(CategoryRequest::class)
            ->withCustomFields()
            ->add('rowOpen1', 'html',[
                'html' => '<div class="row mb-3">'
            ])
            ->add('name', 'text', [
                'label' => trans('core/base::forms.name'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => trans('core/base::forms.name_placeholder'),
                    'data-counter' => 120,
                ],
                'wrapper' => ['class' => 'col-lg-6 col-md-12']
            ])
            ->add('code', 'text', [
                'label' => trans('plugins/warehouse::warehouse.categories.code'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => trans('plugins/warehouse::warehouse.categories.code_placeholder'),
                    'data-counter' => 120,
                ],
                'wrapper' => ['class' => 'col-lg-6 col-md-12']
            ])
            ->add('rowClose1', 'html',[
                'html' => '</div>'
            ])
            ->add('rowOpen2', 'html',[
                'html' => '<div class="row">'
            ])
            ->add('parent_id', 'customSelect', [
                'label' => trans('core/base::forms.parent'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'select-search-full',
                ],
                'choices' => $categories,
                'wrapper' => ['class' => 'col-lg-6 col-md-12']
            ])
            ->add('order', 'number', [
                'label' => trans('core/base::forms.order'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder' => trans('core/base::forms.order_by_placeholder'),
                ],
                'default_value' => 0,
                'wrapper' => ['class' => 'col-lg-6 col-md-12']
            ])
            ->add('rowClose2', 'html',[
                'html' => '</div>'
            ])
            ->add('status', 'customSelect', [
                'label' => trans('core/base::tables.status'),
                'label_attr' => ['class' => 'control-label required'],
                'choices' => BaseStatusEnum::labels(),
            ])
            ->setBreakFieldPoint('status');
    }
}
