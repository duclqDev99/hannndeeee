<?php

namespace Botble\ProductQrcode\BulkActions;


use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Exceptions\DisabledInDemoModeException;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Models\BaseModel;
use Botble\ProductQrcode\Actions\ExportQrCodeProductAction;
use Botble\Table\Abstracts\TableBulkActionAbstract;
use Carbon\Carbon;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Excel;

class ExportBulkAction extends TableBulkActionAbstract
{
    protected $excel;

    public function __construct(Excel $excel)
    {
        $this
            ->label(trans('export'))
            ->confirmationModalButton(trans('export'))
            ->beforeDispatch(function () {
                if (BaseHelper::hasDemoModeEnabled()) {
                    throw new DisabledInDemoModeException();
                }
            })
            ->excel = $excel;
    }

    public function dispatch(BaseModel|Model $model, array $ids) : BaseHttpResponse
    {
        $dataExport =[];
        $product = [];
        $model->newQuery()->whereKey($ids)->each(function (BaseModel $item) use (&$dataExport, &$product) {
            $product[] = ['name' => $item->products['name'] . $item['variation_attributes'],];
            $dataExport[] = array_merge($product, $item->productQrcodeList->pluck('qr_code')->toArray());
        });
        // dd($dataExport);
        $exportAction = new ExportQrCodeProductAction();
        $exportAction->setHeadings([]);

        $exportAction->setCollection($dataExport);

        $data = $this->excel->download($dataExport, 'qrcodes-product-' . Carbon::now()->format('YmdHis') .'.xlsx');

        return BaseHttpResponse::make()->setData($data)->withDeletedSuccessMessage();
    }
}
