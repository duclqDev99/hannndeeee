<?php

namespace Botble\Showroom\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\WarehouseFinishedProducts\Forms\Fields\ProposalProductField;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ShowroomActualIssueDetail extends BaseModel
{
    protected $table = 'showroom_actual_issue_detail';
    protected $fillable = [
        'actual_id',
        'product_id',
        'product_name',
        'sku',
        'price',
        'quantity',
        'qrcode_id',
        'is_batch',
        'reasoon',
        'color',
        'size',
    ];
    public $timestamps = false;
    protected $casts = [
    ];
    public function product($id)
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
    public function qrcode()
    {
        return $this->hasOne(ProductQrcode::class, 'id', 'qrcode_id');
    }

}
