<?php

namespace Botble\Sales\Casts;

use Botble\Base\Facades\BaseHelper;
use Botble\ProcedureOrder\Models\ProcedureOrder;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class DefaultStatusOrder implements CastsAttributes
{
    public function set($model, string $key, $value, array $attributes)
    {
        return ProcedureOrder::where('cycle_point', 'start')->first()->id ?? null;
    }

    public function get($model, string $key, $value, array $attributes)
    {
        return $value;
    }
}
