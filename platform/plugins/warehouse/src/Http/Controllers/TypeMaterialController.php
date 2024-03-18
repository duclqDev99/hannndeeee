<?php

namespace Botble\Warehouse\Http\Controllers;

use Botble\Warehouse\Http\Requests\TypeMaterialRequest;
use Botble\Warehouse\Models\TypeMaterial;
use Illuminate\Http\Request;
use Exception;
use Botble\Warehouse\Tables\TypeMaterialTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Warehouse\Forms\TypeMaterialForm;
use Botble\Base\Forms\FormBuilder;

class TypeMaterialController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this
            ->breadcrumb()
            ->add(trans('Kiểu nguyên phụ liệu'), route('type_material.index'));
    }
    public function index(TypeMaterialTable $table)
    {
        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        $this->pageTitle(trans('plugins/warehouse::type_material.create'));

        return $formBuilder->create(TypeMaterialForm::class)->renderForm();
    }

    public function store(TypeMaterialRequest $request, BaseHttpResponse $response)
    {
        $typeMaterial = TypeMaterial::query()->create($request->input());

        event(new CreatedContentEvent(TYPE_MATERIAL_MODULE_SCREEN_NAME, $request, $typeMaterial));

        return $response
            ->setPreviousUrl(route('type_material.index'))
            ->setNextUrl(route('type_material.edit', $typeMaterial->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(TypeMaterial $typeMaterial, FormBuilder $formBuilder)
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $typeMaterial->name]));

        return $formBuilder->create(TypeMaterialForm::class, ['model' => $typeMaterial])->renderForm();
    }

    public function update(TypeMaterial $typeMaterial, TypeMaterialRequest $request, BaseHttpResponse $response)
    {
        $typeMaterial->fill($request->input());

        $typeMaterial->save();

        event(new UpdatedContentEvent(TYPE_MATERIAL_MODULE_SCREEN_NAME, $request, $typeMaterial));

        return $response
            ->setPreviousUrl(route('type_material.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(TypeMaterial $typeMaterial, Request $request, BaseHttpResponse $response)
    {
        try {
            $typeMaterial->delete();

            event(new DeletedContentEvent(TYPE_MATERIAL_MODULE_SCREEN_NAME, $request, $typeMaterial));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
