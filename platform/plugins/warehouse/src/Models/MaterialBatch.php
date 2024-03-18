<?php

namespace Botble\Warehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Svg\Tag\Rect;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class MaterialBatch extends BaseModel
{
    protected $table = 'wh_material_batchs';

    protected $fillable = [
        'stock_id',
        'receipt_id',
        'batch_code',
        'material_code',
        'material_id',
        'start_qty',
        'quantity',
        'is_order_goods',
        'status',
        'qr_code_base64'
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
    ];

    public function receipt()
    {
        return $this->belongsTo(MaterialReceiptConfirm::class, 'receipt_id', 'id');
    }

    public function receiptGoods()
    {
        return $this->belongsTo(ReceiptPurchaseGoods::class, 'receipt_id', 'id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id', 'id');
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(MaterialWarehouse::class, 'stock_id', 'id');
    }
}
