<?php

namespace Botble\OrderStepSetting\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class StepSetting extends BaseModel
{
    protected $table = 'hd_step_setting';
    protected $fillable = [
        'title',
        'index',
        'is_init',
        'is_ready'
    ];
    protected $casts = [
        'title' => SafeContent::class,
    ];

    public function actionSettings(): HasMany
    {
        return $this->hasMany(ActionSetting::class, 'step_index' , 'index');
    }
}
