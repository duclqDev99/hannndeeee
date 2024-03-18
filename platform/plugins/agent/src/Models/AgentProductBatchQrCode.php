<?php

namespace Botble\Agent\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class AgentProductBatchQrCode extends BaseModel
{
    protected $table = 'agent_batch_qrcodes';
    protected $fillable = [
        'qr_code',
        'base_code_64',
        'batch_id',
        'status',
        'warehouse_id',
        'warehouse_type',
    ];

    protected $casts = [
        'warehouse_type' => SafeContent::class,
        'qr_code' => SafeContent::class,
        'base_code_64' => SafeContent::class,
        'status' => QRStatusEnum::class,
    ];

    public function batch()
    {
        return $this->hasOne(AgentProductBatch::class, 'id', 'batch_id');
    }
    public function warehouse(): MorphTo
    {
        return $this->morphTo('warehouse');
    }

}
