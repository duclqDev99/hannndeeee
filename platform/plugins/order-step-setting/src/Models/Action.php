<?php

namespace Botble\OrderStepSetting\Models;

use Botble\ACL\Models\User;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Enums\OrderStatusEnum as EnumsOrderStatusEnum;
use Botble\OrderStepSetting\Enums\ActionStatusEnum;
use Botble\Sales\Enums\OrderStatusEnum;
use Botble\Sales\Enums\OrderStepStatusEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class Action extends BaseModel
{
    protected $table = 'hd_action';
    protected $fillable = [
        'note',
        'status',
        'handled_at',
        'handler_id',
        'step_id',
        'action_code',
    ];
    protected $attributes = [
        'status' => "pending",
    ];
    protected $casts = [
        'status' => ActionStatusEnum::class,
    ];

    public function scopeWhereShow($q)
    {
        $q->whereRelation('ActionSetting', 'hd_action_setting.is_show', true);
    }

    public function scopeWhereCompleted($q)
    {
        $q->whereHas('actionSetting', fn ($q) => $q->whereRaw('hd_action.status = hd_action_setting.valid_status'));
    }

    public function actionSetting(): BelongsTo
    {
        return $this->belongsTo(ActionSetting::class, 'action_code', 'action_code');
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(Step::class, 'step_id');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handler_id');
    }

    public function getIsCompletedAttribute(){
        return $this->status == $this->actionSetting->valid_status;
    }
}
