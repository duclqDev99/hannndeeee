<?php

namespace Botble\Warehouse\Http\Controllers;

use Botble\ACL\Models\User;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormAbstract;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Warehouse\Forms\CategoryForm;
use Botble\Warehouse\Http\Requests\CategoryRequest;
use Botble\Warehouse\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends BaseController
{
    public function index(FormBuilder $formBuilder, Request $request, BaseHttpResponse $response)
    {
        $this->pageTitle(trans('plugins/warehouse::categories.menu'));

        $categories = Category::query()
            ->wherePublished()
            ->orderByDesc('created_at')
            ->orderBy('order')
            ->get();

        if ($request->ajax()) {
            $data = view('core/base::forms.partials.tree-categories', $this->getOptions(compact('categories')))
                ->render();

            return $response->setData($data);
        }

        Assets::addStylesDirectly(['vendor/core/core/base/css/tree-category.css'])
            ->addScriptsDirectly(['vendor/core/core/base/js/tree-category.js']);

        $form = $formBuilder->create(CategoryForm::class, ['template' => 'core/base::forms.form-tree-category']);
        $form = $this->setFormOptions($form, null, compact('categories'));

        return $form->renderForm();
    }

    public function create(FormBuilder $formBuilder, Request $request, BaseHttpResponse $response)
    {
        $this->pageTitle(trans('plugins/warehouse::categories.create'));

        if ($request->ajax()) {
            return $response->setData($this->getForm());
        }

        return $formBuilder->create(CategoryForm::class)->renderForm();
    }

    public function store(CategoryRequest $request, BaseHttpResponse $response)
    {
        //Check exist code category
        $categoryByCode = Category::where(['code' => $request->input()['code']])->get();
        if(!empty($categoryByCode) && count($categoryByCode) > 0)
        {
            return $response
            ->setError()
            ->setMessage("Mã loại sản phẩm này đã tồn tại. Vui lòng nhập mã khác!!");
        }

        $category = Category::query()->create(
            array_merge($request->input(), [
                'author_id' => Auth::id(),
                'author_type' => User::class,
            ])
        );

        event(new CreatedContentEvent(CATEGORY_MODULE_SCREEN_NAME, $request, $category));

        if ($request->ajax()) {
            if ($request->input('submit') == $response->saveAction) {
                $form = $this->getForm();
            } else {
                $form = $this->getForm($category);
            }

            $response->setData([
                'model' => $category,
                'form' => $form,
            ]);
        }

        return $response
            ->setPreviousUrl(route('product-categories.index'))
            ->setNextUrl(route('product-categories.edit', $category->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(Category $category, FormBuilder $formBuilder, Request $request, BaseHttpResponse $response)
    {
        if ($request->ajax()) {
            return $response->setData($this->getForm($category));
        }

        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $category->name]));

        return $formBuilder->create(CategoryForm::class, ['model' => $category])->renderForm();
    }

    public function update(Category $category, CategoryRequest $request, BaseHttpResponse $response)
    {
        $category->fill($request->input());
        $category->save();

        event(new UpdatedContentEvent(CATEGORY_MODULE_SCREEN_NAME, $request, $category));

        if ($request->ajax()) {
            if ($request->input('submit') == $response->saveAction) {
                $form = $this->getForm();
            } else {
                $form = $this->getForm($category);
            }

            $response->setData([
                'model' => $category,
                'form' => $form,
            ]);
        }

        return $response
            ->setPreviousUrl(route('product-categories.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Category $category, Request $request, BaseHttpResponse $response)
    {
        try {
            $category->delete();

            event(new DeletedContentEvent(CATEGORY_MODULE_SCREEN_NAME, $request, $category));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    protected function getForm(Category|null $model = null): string
    {
        $options = ['template' => 'core/base::forms.form-no-wrap'];

        if ($model) {
            $options['model'] = $model;
        }

        $form = app(FormBuilder::class)->create(CategoryForm::class, $options);

        $form = $this->setFormOptions($form, $model);

        return $form->renderForm();
    }

    protected function setFormOptions(FormAbstract $form, ?Category $model = null, array $options = []): FormAbstract
    {
        if (! $model) {
            $form->setUrl(route('product-categories.create'));
        }

        if (! Auth::user()->hasPermission('product-categories.create') && ! $model) {
            $class = $form->getFormOption('class');
            $form->setFormOption('class', $class . ' d-none');
        }

        $form->setFormOptions($this->getOptions($options));

        return $form;
    }

    protected function getOptions(array $options = []): array
    {
        return array_merge([
            'canCreate' => Auth::user()->hasPermission('product-categories.create'),
            'canEdit' => Auth::user()->hasPermission('product-categories.edit'),
            'canDelete' => Auth::user()->hasPermission('product-categories.destroy'),
            'createRoute' => 'product-categories.create',
            'editRoute' => 'product-categories.edit',
            'deleteRoute' => 'product-categories.destroy',
        ], $options);
    }
}
