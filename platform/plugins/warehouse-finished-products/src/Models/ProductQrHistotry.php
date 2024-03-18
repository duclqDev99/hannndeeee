<?php

namespace Botble\WarehouseFinishedProducts\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\ProductQrcode\Models\ProductQrcode;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ProductQrHistotry extends BaseModel
{
    protected $table = 'wfp_product_qrcodes_histories';
    protected $fillable = [
        'action',
        'description',
        'created_by',
        'qrcode_id',
        'extras',
    ];

    protected $casts = [
        'product_name' => SafeContent::class,
        'status' => BaseStatusEnum::class,
    ];
    public function qrcode()
    {
        return $this->belongsTo(ProductQrcode::class, 'qrcode_id', 'id');
    }

}
