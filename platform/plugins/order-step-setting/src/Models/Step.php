<?php

namespace Botble\OrderStepSetting\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\OrderRetail\Models\Order;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class Step extends BaseModel
{
    protected $table = 'hd_step';
    protected $fillable = [
        'step_index',
        'order_id',
        'is_ready'
    ];

    protected static function booted(): void
    {
        self::deleted(function (Step $step) {
            $step->actions()->each(fn (Action $item) => $item->delete());
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function stepSetting(): BelongsTo
    {
        return $this->belongsTo(StepSetting::class, 'step_index', 'index');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(Action::class, 'step_id');
    }

    public function getIsCompletedAttribute()
    {
        return $this->actions()->whereNot(fn($q) => $q->whereCompleted())->doesntExist();
    }
}
