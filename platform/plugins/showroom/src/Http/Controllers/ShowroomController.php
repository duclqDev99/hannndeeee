<?php

namespace Botble\Showroom\Http\Controllers;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Models\Product;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\Showroom\Enums\ShowroomStatusEnum;
use Botble\Showroom\Forms\ShowroomForm;
use Botble\Showroom\Http\Requests\ShowroomRequest;
use Botble\Showroom\Models\Showroom;
use Botble\Showroom\Models\ShowroomWarehouse;
use Botble\Showroom\Tables\ShowroomTable;
use Exception;
use Illuminate\Http\Request;

class ShowroomController extends BaseController
{
    private $pageTitle;

    public function __construct()
    {
        $this->pageTitle = trans('plugins/showroom::showroom.page_title');
    }

    public function index(ShowroomTable $table)
    {
        PageTitle::setTitle($this->pageTitle['showroom']);

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle($this->pageTitle['create_showrooms']);

        return $formBuilder->create(ShowroomForm::class)->renderForm();
    }

    public function store(ShowroomRequest $request, BaseHttpResponse $response)
    {
        $showroom = Showroom::query()->create($request->input());

        event(new CreatedContentEvent(SHOWROOM_MODULE_SCREEN_NAME, $request, $showroom));

        return $response
            ->setPreviousUrl(route('showroom.index'))
            ->setNextUrl(route('showroom.edit', $showroom->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(Showroom $showroom, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $showroom->name]));

        return $formBuilder->create(ShowroomForm::class, ['model' => $showroom])->renderForm();
    }

    public function update(Showroom $showroom, ShowroomRequest $request, BaseHttpResponse $response)
    {
        $showroom->fill($request->input());

        $showroom->save();

        event(new UpdatedContentEvent(SHOWROOM_MODULE_SCREEN_NAME, $request, $showroom));

        return $response
            ->setPreviousUrl(route('showroom.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Showroom $showroom, Request $request, BaseHttpResponse $response)
    {
        try {
            $showroom->delete();

            event(new DeletedContentEvent(SHOWROOM_MODULE_SCREEN_NAME, $request, $showroom));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function getListShowroomForUser()
    {
        $showrooms = get_showroom_for_user();
        return response()->json([
            'status' => true,
            'data' => $showrooms
        ]);
    }

    public function getAllShowroom()
    {
        $showrooms = Showroom::query()->where('status', BaseStatusEnum::PUBLISHED)->get();
        return response()->json([
            'status' => true,
            'data' => $showrooms
        ]);
    }
    public function getListProductShowroomForUser(Request $request)
    {
        $data = [];
        foreach ($request->showroom as $showrooms) {
            $showroomWarehouses = ShowroomWarehouse::where([
                'showroom_id' => $showrooms['id'],
                'status' => ShowroomStatusEnum::ACTIVE
            ])->whereHas('showroom', function ($q) {
                $q->where('status', BaseStatusEnum::PUBLISHED);
            })->get();
            foreach ($showroomWarehouses as $warehouse) {
                $products = ProductQrcode::select(
                    'warehouse_type',
                    'warehouse_id',
                    'reference_type',
                    'reference_id',
                    'times_product_id',
                    'qr_code',
                    'status'
                )->where([
                    'warehouse_type' => ShowroomWarehouse::class,
                    'warehouse_id' => $warehouse->id,
                    'reference_type' => Product::class
                ])->with('timeCreateQR:id,variation_attributes', 'warehouse:id,name', 'reference:name,id')->get();
                $data = array_merge($data, $products->toArray());
            }
        }
        return response()->json(['data' => $data, 'err' => 0], 200);
    }
}
