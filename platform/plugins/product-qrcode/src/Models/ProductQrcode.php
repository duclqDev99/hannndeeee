<?php

namespace Botble\ProductQrcode\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\HubWarehouse\Models\ActualReceiptQrcode;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\Showroom\Models\ShowroomWarehouse;
use Botble\Warehouse\Models\MaterialWarehouse;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;
use Botble\WarehouseFinishedProducts\Models\ProductBatchDetail;
use Carbon\Carbon;
use Illuminate\Bus\Batch;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ProductQrcode extends BaseModel
{
    protected $table = 'wfp_product_qrcodes';

    protected $appends = ['other_product_name'];

    protected $fillable = [
        'reference_id',
        'status',
        'qr_code',
        'warehouse_type',
        'warehouse_id',
        'created_by',
        'updated_by',
        'times_product_id',
        'reason',
        'identifier',
        'reference_type',
        'base_code_64',
        'production_time',
        'has_exchange'
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($qrcode) {
            if($qrcode->isDirty('status') && $qrcode->warehouse_type == ShowroomWarehouse::class){
                static::updateEcProductQuantity($qrcode);
            }
        });
    }

    protected static function updateEcProductQuantity($qrcode)
    {
        $oldStatus = $qrcode->getOriginal('status');
        $newStatus = $qrcode->status;
        $product = Product::find($qrcode->reference_id);
        if ($newStatus == 'instock') {
            $product?->increment('quantity');
            if ($product?->parentProduct) {
                $product?->parentProduct->first()?->increment('quantity');
            }
        }
        // Từ 'instock' thành trạng thái bất kỳ
        elseif ($oldStatus == 'instock' && $newStatus != 'instock') {
            $product->decrement('quantity');
            // Cập nhật số lượng cho sản phẩm cha nếu có
            if ($product->parentProduct) {
                $product->parentProduct->first()?->decrement('quantity');
            }
        }
    }

    // protected static function updateEcProductQuantity($productId)
    // {
        // $quantity = self::where('reference_id', $productId)
        //     ->where('reference_type', Product::class)
        //     ->where('status', QRStatusEnum::INSTOCK)
        //     ->count();
        // $product = Product::find($productId);
        // if ($product) {
        //     $product->quantity = $quantity;
        //     $product->save();
        //     $product = Product::with('parentProduct')->find($productId);
        //     if ($product && $product->parentProduct) {
        //         $totalQuantity = Product::whereHas('parentProduct', function ($query) use ($product) {
        //             $query->where('ec_product_variations.configurable_product_id', $product->parentProduct[0]->id);
        //         })
        //             ->orWhere('id', $product->parentProduct[0]->id)
        //             ->sum('quantity');
        //         Product::where('id', $product->parentProduct[0]->id)->update(['quantity' => $totalQuantity]);
        //     }
        // }
    // }

    protected $casts = [
        'status' => QRStatusEnum::class,
        'reference_type' => SafeContent::class,
        'base_code_64' => SafeContent::class,
    ];

    public function reference(): MorphTo
    {
        return $this->morphTo('reference', 'reference_type', 'reference_id');
    }
    public function batchParent()
    {
        return $this->belongsTo(ProductBatchDetail::class, 'id', 'qrcode_id');
    }
    // public function product()
    // {
    //     return $this->morphTo('reference', 'reference_type', 'reference_id')
    //         ->where('wfp_product_qrcodes.reference_type', Product::class);
    // }

    // public function batch()
    // {
    //     return $this->morphTo('reference', 'reference_type', 'reference_id')
    //         ->where('wfp_product_qrcodes.reference_type', ProductBatch::class);
    // }

    public function timeCreateQR()
    {
        return $this->belongsTo(TimesExport::class, 'times_product_id', 'id');
    }

    public function getOtherProductNameAttribute()
    {
        return $this->product?->name ?? null;
    }

    public function productBatchDetail()
    {
        return $this->belongsTo(ProductBatchDetail::class, 'id', 'qrcode_id');
    }

    public function warehouse(): MorphTo
    {
        return $this->morphTo('warehouse', 'warehouse_type', 'warehouse_id');
    }
    public function base64()
    {
        return $this->hasOne('Illuminate\Database\Eloquent\Model', 'product_qrcode_id')
            ->setTable('wfp_product_qrcodes_base64');
    }

    public function materialWarehouse()
    {
        return $this->belongsTo(MaterialWarehouse::class, 'warehouse_id', 'id');
    }

    public function batch()
    {
        if ($this->reference_type == ProductBatch::class) {
            return $this->hasOne(ProductBatch::class, 'id', 'reference_id');
        }
        return null;
    }


    public function timeReceiptHub()
    {
        return $this->hasOne(ActualReceiptQrcode::class, 'qrcode_id');
    }
}
