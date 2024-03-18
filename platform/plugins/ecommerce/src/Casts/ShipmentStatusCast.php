<?php

namespace Botble\Ecommerce\Casts;

use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Enums\GHTKStatusEnum;
use Botble\ProcedureOrder\Models\ProcedureOrder;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class ShipmentStatusCast implements CastsAttributes
{
    public function set($model, string $key, $value, array $attributes)
    {
        return $value;
    }

    public function get($model, string $key, $value, array $attributes)
    {
        return app(GHTKStatusEnum::class)->make($value);
    }
}
