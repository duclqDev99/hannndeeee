<?php

namespace Botble\WarehouseFinishedProducts\Http\Controllers;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\GroupedProduct;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Ecommerce\Models\ProductVariationItem;
use Botble\Ecommerce\Services\Products\StoreAttributesOfProductService;
use Botble\Ecommerce\Services\Products\StoreProductService;
use Botble\Ecommerce\Services\StoreProductTagService;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Http\Requests\ProductRequest;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProductBatchStatusEnum;
use Botble\WarehouseFinishedProducts\Http\Requests\FinishedProductsRequest;
use Botble\WarehouseFinishedProducts\Models\FinishedProducts;
use Botble\Base\Facades\PageTitle;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;
use Botble\WarehouseFinishedProducts\Models\ProductBatchDetail;
use Botble\WarehouseFinishedProducts\Models\QuantityProductInStock;
use Botble\WarehouseFinishedProducts\Models\WarehouseUser;
use Botble\WarehouseFinishedProducts\Tables\ProductDetailTable;
use Illuminate\Http\Request;
use Exception;
use Botble\WarehouseFinishedProducts\Tables\FinishedProductsTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\WarehouseFinishedProducts\Forms\FinishedProductsForm;
use Botble\Base\Forms\FormBuilder;
use Botble\WarehouseFinishedProducts\Models\WarehouseFinishedProducts;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinishedProductsController extends BaseController
{
    public function index(FinishedProductsTable $table)
    {
        Assets::addScripts(['bootstrap-editable'])

            ->addStyles(['bootstrap-editable'])->addScriptsDirectly(
                [
                    'vendor/core/plugins/warehouse-finished-products/js/detail-product.js ',

                ]
            );
        PageTitle::setTitle('Danh sách thành phẩm');
        return $table->renderTable();
    }


    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/warehouse-finished-products::warehouse-finished-products.finished-products.create'));
        return $formBuilder->create(FinishedProductsForm::class)->renderForm();
    }

    public function store(
        ProductRequest $request,
        StoreProductService $service,
        StoreAttributesOfProductService $storeAttributesOfProductService,
        StoreProductTagService $storeProductTagService
    ) {
        $product = new Product();
        $product->status = $request->input('status');
        if (EcommerceHelper::isEnabledSupportDigitalProducts() && $productType = $request->input('product_type')) {
            $product->product_type = $productType;
        }

        $product = $service->execute($request, $product);
        $storeProductTagService->execute($request, $product);

        $addedAttributes = $request->input('added_attributes', []);

        if ($request->input('is_added_attributes') == 1 && $addedAttributes) {
            $storeAttributesOfProductService->execute(
                $product,
                array_keys($addedAttributes),
                array_values($addedAttributes)
            );

            $variation = ProductVariation::query()->create([
                'configurable_product_id' => $product->getKey(),
            ]);

            foreach ($addedAttributes as $attribute) {
                ProductVariationItem::query()->create([
                    'attribute_id' => $attribute,
                    'variation_id' => $variation->getKey(),
                ]);
            }

            $variation = $variation->toArray();

            $variation['variation_default_id'] = $variation['id'];

            $variation['sku'] = $product->sku;
            $variation['auto_generate_sku'] = true;

            $variation['images'] = array_filter((array) $request->input('images', []));

            $this->postSaveAllVersions(
                [$variation['id'] => $variation],
                $product->getKey(),
                $this->httpResponse()
            );
        }

        if ($request->has('grouped_products')) {
            GroupedProduct::createGroupedProducts(
                $product->getKey(),
                array_map(function ($item) {
                    return [
                        'id' => $item,
                        'qty' => 1,
                    ];
                }, array_filter(explode(',', $request->input('grouped_products', ''))))
            );
        }

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('finished-products.index'))
            ->setNextUrl(route('finished-products.edit', $product->getKey()))
            ->withCreatedSuccessMessage();
    }
    public function edit(Product $finishedProducts, FormBuilder $formBuilder, Request $request)
    {
        if ($finishedProducts->is_variation) {
            abort(404);
        }

        PageTitle::setTitle(trans('plugins/warehouse-finished-products::warehouse-finished-products.finished-products.edit', ['name' => $finishedProducts->name]));

        event(new BeforeEditContentEvent($request, $finishedProducts));

        return FinishedProductsForm::createFromModel($finishedProducts)->renderForm();
    }
    public function update(
        Product $finishedProducts,
        ProductRequest $request,
        StoreProductService $service,
        StoreProductTagService $storeProductTagService
    ) {
        $finishedProducts->status = $request->input('status');

        $product = $service->execute($request, $finishedProducts);
        $storeProductTagService->execute($request, $product);

        if ($request->has('variation_default_id')) {
            ProductVariation::query()
                ->where('configurable_product_id', $product->getKey())
                ->update(['is_default' => 0]);

            $defaultVariation = ProductVariation::query()->find($request->input('variation_default_id'));
            if ($defaultVariation) {
                $defaultVariation->is_default = true;
                $defaultVariation->save();
            }
        }

        $addedAttributes = $request->input('added_attributes', []);

        if ($request->input('is_added_attributes') == 1 && $addedAttributes) {
            $result = ProductVariation::getVariationByAttributesOrCreate($product->getKey(), $addedAttributes);

            /**
             * @var ProductVariation $variation
             */
            $variation = $result['variation'];

            foreach ($addedAttributes as $attribute) {
                ProductVariationItem::query()->create([
                    'attribute_id' => $attribute,
                    'variation_id' => $variation->getKey(),
                ]);
            }

            $variation = $variation->toArray();
            $variation['variation_default_id'] = $variation['id'];

            $product->productAttributeSets()->sync(array_keys($addedAttributes));

            $variation['sku'] = $product->sku;
            $variation['auto_generate_sku'] = true;

            $this->postSaveAllVersions([$variation['id'] => $variation], $product->getKey(), $this->httpResponse());
        } elseif ($product->variations()->count() === 0) {
            $product->productAttributeSets()->detach();
        }

        if ($request->has('grouped_products')) {
            GroupedProduct::createGroupedProducts(
                $product->getKey(),
                array_map(function ($item) {
                    return [
                        'id' => $item,
                        'qty' => 1,
                    ];
                }, array_filter(explode(',', $request->input('grouped_products', ''))))
            );
        }

        $relatedProductIds = $product->variations()->pluck('product_id')->all();

        Product::query()->whereIn('id', $relatedProductIds)->update(['status' => $product->status]);

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('finished-products.index'))
            ->withUpdatedSuccessMessage();
    }



    public function destroy(FinishedProducts $finishedProducts, Request $request, BaseHttpResponse $response)
    {
        try {
            $finishedProducts->delete();

            event(new DeletedContentEvent(FINISHED_PRODUCTS_MODULE_SCREEN_NAME, $request, $finishedProducts));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    // public function getAllProduct($id)
    // {
    //     $products = QuantityProductInStock::where('stock_id', $id)
    //         ->where('quantity', '>', 0)->with('product.variationProductAttributes')->get();
    //     return response()->json(['data' => $products], 200);
    // }
    public function getAllProduct($id, $type)
    {
        // if ($type == 1) {
        //     $products = ProductBatch::with([
        //         'product' => function ($query) {
        //             $query->where('status', 'published');
        //         },
        //         'product.variationInfo',
        //         'product.variationProductAttributes',
        //         'product.parentProduct'


        //     ])
        //         ->where([
        //             'warehouse_type' => WarehouseFinishedProducts::class,
        //             'warehouse_id' => $id,
        //             'status' => ProductBatchStatusEnum::INSTOCK
        //         ])
        //         ->select('product_parent_id')
        //         ->selectRaw('COUNT(*) as batch_count')
        //         ->groupBy('product_parent_id')
        //         ->get();
        // } else {
        $products = QuantityProductInStock::where('stock_id', $id)->where('quantity', '>', 0)->with([
            'product.variationInfo',
            'product.variationProductAttributes',
            'product.parentProduct'
        ])
            ->get();
        // }

        return response()->json(['data' => $products], 200);
    }
    public function getProductForWarehouseInBatch($id)
    {
        $products = ProductBatch::where('quantity', '>', 0)->with([
            'product' => function ($query) {
                $query->where('status', 'published');
            },
            'product.variationInfo',
            'product.variationProductAttributes',
            'getQRCode'
        ])
            ->where([
                'warehouse_type' => WarehouseFinishedProducts::class,
                'warehouse_id' => $id
            ])
            ->whereHas('getQRCode', function ($q) {
                $q->where('status', QRStatusEnum::INSTOCK);
            })
            ->select('product_parent_id')
            ->selectRaw('COUNT(*) as batch_count')
            ->groupBy('product_parent_id')
            ->get();
        return response()->json(['data' => $products], 200);
    }
    public function checkQuantity($warehouseId, $id)
    {
        $warehouse = WarehouseFinishedProducts::find($warehouseId);

        if (!$warehouse) {
            return response()->json(['message' => 'Warehouse not found'], 404);
        }

        $product = $warehouse->products()->where('product_id', $id)->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found in this warehouse'], 404);
        }

        return response()->json([
            'data' => [
                'product_id' => $id,
                'warehouse_id' => $warehouseId,
                'quantity' => $product->pivot->quantity
            ]
        ], 200);
    }
    public function detail(string|int $id, ProductDetailTable $table, Request $request)
    {
        $products = Product::find($id);

        $productDetail = [
            'products' => [],
        ];

        foreach ($products->variations as $product) {
            $arrAttribute = $product->variationItems;
            list($color, $size, $stock, $total) = $this->extractColorAndSize($arrAttribute, $product->product->id);
            if ($stock) {
                $productDetail['products'][] = [
                    'id' => $product->product->id,
                    'name' => $product->product->name,
                    'price' => $product->product->price,
                    'color' => $color,
                    'size' => $size,
                    'quantity' => $product->product->quantity,
                    'stock' => $stock,
                    'total' => $total
                ];
            }
        }


        return response()->json($productDetail, 200);
        // PageTitle::setTitle('Chi tiết sản phẩm ' . $product->name);
        // $request->merge(['id' => $id]);
        // return $table->renderTable();
    }
    public function getAllListProduct($id, Request $request)
    {
        $select = [
            'id',
            'name',
            'images',
            'sku',
            'is_variation',
            'status'
        ];
        $keySearch = $request->search;
        $type = $request->type;
        if ($keySearch != "") {
            $products = Product::select($select)
                ->where('status', BaseStatusEnum::PUBLISHED)
                ->where('is_variation', 1)
                ->where(function ($q) use ($keySearch) {
                    $q->where('name', 'LIKE', "%" . $keySearch . "%")
                        ->orWhere('sku', 'LIKE', "%" . $keySearch . "%");
                })
                ->orWhereHas('parentProduct', function ($query) use ($keySearch) {
                    $query->where('name', 'LIKE', "%" . $keySearch . "%")
                        ->orWhere('sku', 'LIKE', "%" . $keySearch . "%");
                })
                ->with('productAttribute', 'parentProduct')->limit(50)->get();
        } else {

            $products = Product::select($select)->where(['status' => BaseStatusEnum::PUBLISHED, 'is_variation' => 1])->with('productAttribute', 'parentProduct')->limit(50)->get();
        }
        if ($type && $type == 'warehouse-finished') {
            $quantity = QuantityProductInStock::where('stock_id', $id)->get();
        } else {
            $quantity = \Botble\HubWarehouse\Models\QuantityProductInStock::where('stock_id', $id)->get();
        }
        foreach ($products as $product) {
            $found = false; // Biến kiểm tra xem sản phẩm có tồn tại trong $quantity không
            foreach ($quantity as $qty) {
                if ($product->id == $qty->product_id) {
                    $product->setAttribute('quantity_in_stock', $qty->quantity);
                    $found = true; // Đánh dấu sản phẩm đã được tìm thấy trong $quantity
                    break; // Dừng vòng lặp sau khi tìm thấy sản phẩm
                }
            }
            if (!$found) {
                // Nếu không tìm thấy sản phẩm trong $quantity, thiết lập quantity_in_stock = 0
                $product->setAttribute('quantity_in_stock', 0);
            }
        }
        return response()->json([
            'product' => $products
        ]);
    }
    private function extractColorAndSize($arrAttribute, $product_id)
    {
        $color = '';
        $size = '';
        $stock = [];
        $total = 0;
        $quantityInstocks = QuantityProductInStock::where('product_id', $product_id)->get();
        foreach ($quantityInstocks as $quantityInstock) {
            if (!Auth::user()->hasPermission('warehouse-finished-products.warehouse-all')) {
                $warehouseUsers = WarehouseUser::where('user_id', \Auth::id())->get();
                $warehouseIds = $warehouseUsers->pluck('warehouse_id')->toArray();
                if (in_array($quantityInstock->warehouse->id, $warehouseIds)) {
                    $stock[] = [
                        'stock' => $quantityInstock->warehouse->name,
                        'quantity' => $quantityInstock->quantity
                    ];
                    $total += $quantityInstock->quantity;
                }
            } else {
                $stock[] = [
                    'stock' => $quantityInstock->warehouse->name,
                    'quantity' => $quantityInstock->quantity
                ];
                $total += $quantityInstock->quantity;
            }
        }
        if (count($arrAttribute) === 2) {
            $size = ($arrAttribute[1]->attribute->title);
            $color = ($arrAttribute[0]->attribute->title);
        }
        return [$color, $size, $stock, $total];
    }
}
