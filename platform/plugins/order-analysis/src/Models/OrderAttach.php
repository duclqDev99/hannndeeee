<?php

namespace Botble\OrderAnalysis\Models;

use Botble\ACL\Models\User;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\OrderAnalysis\Enums\OrderAnalysisStatusEnum;
use Botble\OrderAnalysis\Enums\OrderAttachStatusEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class OrderAttach extends BaseModel
{
    protected $table = 'hd_order_attachs';
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'attach_type',
        'attach_id',
        'status'
    ];

    protected $casts = [
        'attach_type' => SafeContent::class,
        'status' => OrderAttachStatusEnum::class,
    ];

    public function attachFile(): MorphTo
    {
        return $this->morphTo('order_analyses', 'attach_type', 'attach_id');
    }
}
