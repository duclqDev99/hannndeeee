<?php

namespace Botble\ProductQrcode\Models;

use App\Models\User;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class TimesExport extends BaseModel
{
    protected $table = 'wfp_times_export_product_qrcodes';

    protected $fillable = [
        'product_id',
        'quantity_product',
        'times_export',
        'created_by',
        'updated_by',
        'variation_attributes',
        'title',
        'description',
    ];

    protected $casts = [
        'title' => SafeContent::class,
    ];

    public function products(): BelongsTo
    {
        return $this->belongsTo(Product::class,'product_id', 'id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by', 'id');
    }


    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class,'updated_by', 'id');
    }

    public function productQrcodeList(): HasMany
    {
        return $this->hasMany(ProductQrcode::class,'times_product_id', 'id');
    }

    public function QrcodeTemporary(): HasMany
    {
        return $this->hasMany(QrcodeTemporary::class,'times_product_id', 'id');
    }

    // Định nghĩa accessor cho 'production_time'
    public function getProductionTimeAttribute($value)
    {
        if (is_null($value)) {
            return '__';
        }
        return Carbon::parse($value)->format('m-Y');
    }
}
