<?php

namespace Botble\Warehouse\Http\Controllers;

use Botble\Media\Facades\RvMedia;
use Botble\Warehouse\Enums\MaterialStatusEnum;
use Botble\Warehouse\Http\Requests\MaterialRequest;
use Botble\Warehouse\Models\MaterialWarehouse;
use Botble\Warehouse\Models\Material;
use Botble\Base\Facades\PageTitle;
use Botble\Warehouse\Services\StorageTypeMaterialService;
use Botble\Warehouse\Tables\MaterialDetailTable;
use Illuminate\Http\Request;
use Exception;
use Botble\Warehouse\Tables\MaterialTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Warehouse\Forms\MaterialForm;
use Botble\Base\Forms\FormBuilder;
use Botble\Warehouse\Actions\ImportMaterialAction;

class MaterialController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this
            ->breadcrumb()
            ->add(trans('Nguyên phụ liệu'), route('material.index'));
    }
    public function index(MaterialTable $table)
    {
        Assets::addScriptsDirectly(
            [
                'vendor/core/plugins/warehouse/js/import-material.js',
                'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.5/xlsx.full.min.js',
            ]
        )->addScripts(['blockui']);
        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        $this->pageTitle(trans('plugins/warehouse::material.create'));

        return $formBuilder->create(MaterialForm::class)->renderForm();
    }

    public function store(
        MaterialRequest $request,
        BaseHttpResponse $response,
        StorageTypeMaterialService $TypeMaterialService
    ) {

        $material = Material::query()->create($request->input());
        event(new CreatedContentEvent(MATERIAL_MODULE_SCREEN_NAME, $request, $material));
        $TypeMaterialService->execute($request, $material);

        return $response
            ->setPreviousUrl(route('material.index'))
            ->setNextUrl(route('material.edit', $material->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'))->setData($material);
    }

    public function edit(Material $material, FormBuilder $formBuilder)
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $material->name]));

        return $formBuilder->create(MaterialForm::class, ['model' => $material])->renderForm();
    }

    public function update(
        Material $material,
        MaterialRequest $request,
        BaseHttpResponse $response,
        StorageTypeMaterialService $TypeMaterialService
    ) {
        $material->fill($request->input());

        $material->save();

        event(new UpdatedContentEvent(MATERIAL_MODULE_SCREEN_NAME, $request, $material));
        $TypeMaterialService->execute($request, $material);
        return $response
            ->setPreviousUrl(route('material.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Material $material, Request $request, BaseHttpResponse $response)
    {
        try {
            $material->delete();

            event(new DeletedContentEvent(MATERIAL_MODULE_SCREEN_NAME, $request, $material));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function getMaterialByWarehouse($id)
    {
        $materials = MaterialWarehouse::with(['materials' => function ($query) {
            $query->where('status', MaterialStatusEnum::ACTIVE);
        }])->find($id);
        return response()->json($materials->materials, 200);
    }
    public function getThumbAttribute($value)
    {
        return RvMedia::getImageUrl($value, null, false);
    }
    public function import(ImportMaterialAction $action, Request $request)
    {
        $json = (array) $request->input('json_data', []);
        return $action->run($json);
    }
    public function detail(int|string $id, MaterialDetailTable $table, Request $request)
    {
        $materials = Material::find($id);
        $this->pageTitle('Chi tiết nguyên phụ liệu ' . $materials->name);
        $request->merge(['id' => $id]);
        return $table->renderTable();
    }
}
