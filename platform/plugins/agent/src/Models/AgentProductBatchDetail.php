<?php
namespace Botble\Agent\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\ProductQrcode\Models\ProductQrcode;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class AgentProductBatchDetail extends BaseModel
{
    protected $table = 'agent_product_batch_detail';
    protected $fillable = [
        'batch_id',
        'product_id',
        'qrcode',
        'product_id',
        'product_name',
        'sku',
    ];
    public $timestamps = false;

    public function productBatch()
    {
        return $this->belongsTo(AgentProductBatch::class, 'batch_id', 'id');
    }
    public function statusQrCode()
    {
        return $this->belongsTo(ProductQrcode::class, 'qrcode', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

}
