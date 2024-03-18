<?php

namespace Botble\ProductQrcode\Http\Controllers;

use Botble\ProductQrcode\Http\Requests\ProductQrcodeRequest;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use GPBMetadata\Google\Api\Auth;
use Illuminate\Http\Request;
use Exception;
use Botble\ProductQrcode\Tables\ProductQrcodeTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\ProductQrcode\Forms\ProductQrcodeForm;
use Botble\Base\Forms\FormBuilder;
use Botble\Ecommerce\Http\Requests\SearchProductAndVariationsRequest;
use Botble\Ecommerce\Http\Resources\AvailableProductResource;
use Botble\Ecommerce\Models\Product;
use Botble\ProductQrcode\Models\TimesExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Botble\ProductQrcode\Actions\ExportQrCodeFormViewAction;
use Botble\ProductQrcode\Exports\ProductQrcodeExport;
use Botble\ProductQrcode\Models\QrcodeTemporary;
use Botble\ProductQrcode\Tables\ProductQrcodeDetaiTable;
use Maatwebsite\Excel\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Crypt;
use Botble\ProductQrcode\Helpers\random;
use Botble\ProductQrcode\Http\Requests\createdQrcodeRequest;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

class ProductQrcodeController extends BaseController
{
    protected $excel;
    protected $random;
    protected $limitChunk;
    protected $limitStringRandum;
    private $encryptionKey;


    public function __construct(Excel $excel, random $random)
    {
        $this->excel = $excel;
        $this->random = $random;
        $this->limitChunk = 500;
        $this->limitStringRandum = 5;
        $this->encryptionKey = env('ENCRYPTION_QR_KEY');
    }

    public function index(ProductQrcodeTable $table)
    {
        Assets::addScriptsDirectly([
            'vendor/core/plugins/product-qrcode/js/script.js',
            'vendor/core/plugins/product-qrcode/js/print-product-qr-code.js',
        ]);
        PageTitle::setTitle(trans('plugins/product-qrcode::product-qrcode.name'));

        // return $table->renderTable();
        return $table->render('plugins/product-qrcode::table/product-qrcode');
    }

    public function create(FormBuilder $formBuilder)
    {
        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly([
                'vendor/core/plugins/ecommerce/libraries/jquery.textarea_autosize.js',
                'vendor/core/plugins/product-qrcode/js/search-product.js',
            ])
            ->addScripts(['input-mask']);

        Assets::usingVueJS();

        $this->pageTitle(trans('Tạo mã QR sản phẩm'));

        return view('plugins/product-qrcode::create-qr.index');
    }

    public function store(ProductQrcodeRequest $request, BaseHttpResponse $response)
    {
        $productQrcode = ProductQrcode::query()->create($request->input());

        event(new CreatedContentEvent(PRODUCT_QRCODE_MODULE_SCREEN_NAME, $request, $productQrcode));

        return $response
            ->setPreviousUrl(route('product-qrcode.index'))
            ->setNextUrl(route('product-qrcode.edit', $productQrcode->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(ProductQrcode $productQrcode, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $productQrcode->name]));

        return $formBuilder->create(ProductQrcodeForm::class, ['model' => $productQrcode])->renderForm();
    }

    public function update(ProductQrcode $productQrcode, ProductQrcodeRequest $request, BaseHttpResponse $response)
    {
        $productQrcode->fill($request->input());

        $productQrcode->save();

        event(new UpdatedContentEvent(PRODUCT_QRCODE_MODULE_SCREEN_NAME, $request, $productQrcode));

        return $response
            ->setPreviousUrl(route('product-qrcode.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function getProductById(Request $request)
    {
        $productId = $request->id;
        $with = [
            'productCollections',
            'variationInfo',
            'variationInfo.configurableProduct',
            'variationProductAttributes',
        ];

        $products = Product::query()
            ->with($with)
            ->find($productId);
        if ($request->wantsJson() || $request->expectsJson()) {
            return new JsonResponse(['data' => $products], 200);
        }
        return $products;
    }

    public function createQrCode(createdQrcodeRequest $request, BaseHttpResponse $response)
    {
        $dataForm = $request->all()['data'];
        $products = $dataForm['products'];
        $title = $dataForm['title'];
        $dataFormProcessed = [];
        $dataCollection = [];
        $dataHeadings = [];
        $dataTimesExport = [];
        $user = auth()->guard()->user();
        $today = Carbon::now()->format('Y-m-d H:i:s');
        DB::beginTransaction();

        try {
            foreach ($products as $product) {
                $dataQrCodeArr = [];
                $dataTimesExport = [
                    'product_id' => $product['product_id'],
                    'quantity_product' => $product['select_qty'],
                    'title' => $title,
                    'variation_attributes' => $product['variation_attributes'],
                    'times_export' => 1,
                    'created_by' => $user->id,
                    'created_at' => $today,
                    'description' => $dataForm['description'],
                ];
                $modelTimeExport = TimesExport::create($dataTimesExport);

                $historiesCreatedStringRandum = [];
                for ($i = 0; $i < $product['select_qty']; $i++) {
                    $qrCode = $this->generateStringCodeInQrcode(($i + 1));
                    if (in_array($qrCode, $historiesCreatedStringRandum)) {
                        throw new Exception("Dữ liệu trùng lặp");
                    }
                    $dataFormProcessed[] = [
                        'reference_id' => $product['product_id'],
                        'reference_type' => Product::class,
                        'status' => $product['status'],
                        'times_product_id' => $modelTimeExport->id,
                        'qr_code' => $qrCode,
                        'product_type' => $product['product_type'] == 'product' ? Product::class : null,
                        'created_by' => $user->id,
                        'identifier' => $this->random->generateUniqueIdentifier(),
                    ];
                    $dataQrCodeArr[] = $qrCode;
                    $historiesCreatedStringRandum[] = $qrCode;
                }
                $dataHeadings[] = $product['name'] . $product['variation_attributes'];
                $dataCollection[] = $dataQrCodeArr;
            }
            if (count($dataFormProcessed) > 0) {
                $chunks = array_chunk($dataFormProcessed, 500);
                foreach ($chunks as $chunk) {
                    ProductQrcode::insert($chunk);
                }
            }
            DB::commit();

            $exportAction = new ExportQrCodeFormViewAction();
            $exportAction->setCollection($dataCollection);

            $exportAction->setHeadings($dataHeadings);
            return $this->excel->download($exportAction, 'qrcodes-product-' . Carbon::now()->format('YmdHis') . '.xlsx');
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage(), 1);
        }
    }

    private function generateStringCodeInQrcode($index)
    {
        $timestamp = (int) (microtime(true) * 1000);
        $randomStringFirst = Str::random($this->limitStringRandum);
        $randomStrinLast = Str::random($this->limitStringRandum);
        $randomIntFirst = substr($timestamp, 0, 7) + $index;
        $randomIntLast = substr($timestamp, -7) + $index;
        return ($randomStringFirst . $randomIntFirst . $randomStrinLast . $randomIntLast);
    }

    public function exportQrCode(Request $request, BaseHttpResponse $response)
    {
        $queryArray = $request->query()['ids'];
        $ids = (array)json_decode($queryArray);
        try {
            $dataHeadings = [];
            if (empty($ids)) {
                return $response->setError()->setMessage(trans('export thất bại, bạn chưa chọn sản phẩm!!'));
            }
            $ids = array_map('intval', $ids);
            $listQR = ProductQrcode::with('product:id,name')->with('timeCreateQR:id,variation_attributes')->whereHas('timeCreateQR', function ($q) use ($ids) {
                return $q->whereIn('id', $ids);
            })->get();
            $grouped = $listQR->groupBy(function ($item, $key) {
                return $item['product']['name'] . ' ' . $item['timeCreateQR']['variation_attributes'];
            });

            $dataCollection = $grouped->map(function ($items, $productName) use (&$dataHeadings) {
                $dataHeadings[] =  $productName;
                return $items->pluck('qr_code')->toArray();
            })->values()->toArray();

            TimesExport::whereIn('id', $ids)->increment('times_export');

            $exportAction = new ExportQrCodeFormViewAction();
            $exportAction->setCollection($dataCollection);
            $exportAction->setHeadings($dataHeadings);

            return  $this->excel->download($exportAction, 'qrcodes-product-' . Carbon::now()->format('YmdHis') . '.xlsx');
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function exportQrCodeTemporary(Request $request, BaseHttpResponse $response)
    {

        $dataReq =  $request->all();
        $id = $request->query()['id'];
        $chunkSize = $dataReq['chunkSize'];
        $desiredChunkIndex = $dataReq['next'];
        $offset = $chunkSize * $desiredChunkIndex;
        try {
            $productQrcode = ProductQrcode::with('reference')->where('times_product_id', $id);

             if(!request()->user()->hasPermission('product-qrcode.in-super')){
                 $productQrcode->where('print_times_count', 0);
             }

            $productQrcode = $productQrcode->skip($offset)
            ->take($chunkSize)
            ->get();

            if(count($productQrcode) == 0){
                return view('core/base::errors.404')->render();
//                return response()->json(['message' => 'Không tìm thấy bản ghi productQrcode nào.'], 404);
            }
            $productQrcodeJson[] = [
                "id" => "id",
                "sku" => "sku",
                "name" => "name",
                "ingredient" => "ingredient",
                "price" => "price",
                "qr_code" => "qr_code",
                "identifier" => "identifier",
                "item_diameter" => "item_diameter",
            ];
            $productQrcodeId = [];
            $attrProduct = [];
            foreach ($productQrcode as $val) {
                if(count($attrProduct) <= 0){
                    $arrayAttr = [];
                    foreach($val?->reference->variationProductAttributes as $productAttributeSets){
                        $attrProduct[$productAttributeSets->attribute_set_slug] = $productAttributeSets?->title;
                        $arrayAttr[ $productAttributeSets->attribute_set_slug ] =  $productAttributeSets->attribute_set_slug;
                        $productQrcodeJson[0] = $productQrcodeJson[0] + $arrayAttr;
                    }
                }

                $parent = $val?->reference?->parentProduct->first();
                $val?->reference?->price;
                $itemDiameter = $val?->reference?->parentProduct?->first()?->item_diameter ?? null;
                $array = [
                    "id" => $val->id,
                    "sku" => $parent?->sku,
                    "name" => $val?->reference->name,
                    "ingredient" => $parent?->ingredient,
                    "price" => "Giá: " . number_format($val?->reference?->price, 0, '.', ',') . " VNĐ",
                    "qr_code" => $val?->qr_code,
                    "identifier" => $val?->identifier,
                    "item_diameter" => $itemDiameter,
                ];
                foreach($attrProduct as $key => $attr){
                    $array[$key] = $attr;
                }
                $productQrcodeJson[] = $array;
                $productQrcodeId[] = $val->id;
            }


            ProductQrcode::whereIn('id', $productQrcodeId)->increment('print_times_count');
            $exportAction = new ProductQrcodeExport($productQrcodeJson);

            return  $this->excel->download($exportAction, 'qr' . '.xlsx');
            // return (new Collection([
            //     [1, 2, 3],
            //     [1, 2, 3]
            // ]))->downloadExcel('my-collection.xlsx');
            // $fileName = 'qr.json';
            // return response(json_encode($productQrcodeJson, JSON_UNESCAPED_UNICODE))
            //     ->withHeaders([
            //         'Content-Disposition' => 'attachment; filename=' . $fileName,
            //         'Content-Type' => 'application/json',
            //     ]);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 1);
        }
    }

    public function ajaxPostQrScan(Request $request)
    {
        try {
            $qrDecrypt = $request->qr_code;
            $productQrCode = $qrDecrypt ? ProductQrcode::where('qr_code', $qrDecrypt)->first() : false;
            if (!$productQrCode) throw new \Exception('Mã QR không tồn tại trên hệ thống');
            switch ($productQrCode->reference_type) {
                case Product::class:
                    $productQrCode->loadMissing([
                        'reference:id,name,price,sale_price,production_time',
                        'reference.parentProduct:id',
                        'timeCreateQR:id,quantity_product,variation_attributes,times_export',
                        'warehouse',
                        'batchParent.productBatch',
                        'timeReceiptHub'
                    ]);
                    break;
                case ProductBatch::class:
                    $productQrCode->loadMissing([
                        'warehouse',
                        'reference',
                        'reference.productInBatch.product:name,sku,id',
                    ]);
                    break;
            }
            return response()->json([
                'success' => 1,
                'message' => 'Quét thành công',
                'data' => $productQrCode,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => 0,
                'message' =>  $e->getMessage(),
            ], 400);
        }
    }

    public function getQrcodeById(Request $request)
    {
//        dd($request->all()['dataUrlConfirm']);
        $dataUrlConfirm = $request->all()['dataUrlConfirm'];
        $qrcodeTemporary = TimesExport::find($request->all()['id']);
        if(!request()->user()->hasPermission('product-qrcode.in-super')){
            $countQrOfUnprinted = ProductQrcode::query()->where('times_product_id', $qrcodeTemporary->id)->where('print_times_count', 0)->count();
            $qrcodeTemporary->quantity_product = $countQrOfUnprinted;
        }
        $export = route('product-qrcode.export-temporary', 'id=' . $request->all()['id']);
        return view('plugins/product-qrcode::modals.print-product-qrcode', compact('qrcodeTemporary', 'export', 'dataUrlConfirm'));
    }

    public function getAllProductAndVariations(
        SearchProductAndVariationsRequest $request,
        BaseHttpResponse $response
    ): BaseHttpResponse {
        $selectedProducts = collect();
        if ($productIds = $request->input('product_ids', [])) {
            $selectedProducts = Product::query()
                ->wherePublished()
                ->whereIn('id', $productIds)
                ->with(['variationInfo.configurableProduct'])
                ->get();
        }

        $keyword = $request->input('keyword');

        $availableProducts = Product::query()
            ->select(['ec_products.*'])
            ->where('is_variation', false)
            ->wherePublished()
            ->with(['variationInfo.configurableProduct'])
            ->when($keyword, function ($query) use ($keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query
                        ->where('name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('sku', 'LIKE', '%' . $keyword . '%');
                });
            });

        if (is_plugin_active('marketplace') && $selectedProducts->count()) {
            $selectedProducts = $selectedProducts->map(function ($item) {
                if ($item->is_variation) {
                    $item->store_id = $item->original_product->store_id;
                }

                if (! $item->store_id) {
                    $item->store_id = 0;
                }

                return $item;
            });
            $storeIds = array_unique($selectedProducts->pluck('store_id')->all());
            $availableProducts = $availableProducts->whereIn('store_id', $storeIds)->with(['store']);
        }

        $availableProducts = $availableProducts->simplePaginate(5);

        return $response->setData(AvailableProductResource::collection($availableProducts)->response()->getData());
    }
}
