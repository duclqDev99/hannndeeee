<?php

namespace Botble\OrderAnalysis\Models;

use Botble\ACL\Models\User;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\OrderAnalysis\Enums\OrderAnalysisStatusEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class OrderAnalysis extends BaseModel
{
    protected $table = 'order_analyses';

    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'code' => SafeContent::class,
        'name' => SafeContent::class,
        'description' => SafeContent::class,
        'status' => OrderAnalysisStatusEnum::class,
    ];

    public function analysisDetails(): HasMany
    {
        return $this->hasMany(analysisDetail::class, 'analysis_order_id', 'id');
    }
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by', 'id');
    }
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by', 'id');
    }
}
