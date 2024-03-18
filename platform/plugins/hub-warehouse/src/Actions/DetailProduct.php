<?php

namespace Botble\HubWarehouse\Actions;

use Botble\Table\Actions\Action;

class DetailProduct extends Action
{

    public static function make(string $name = 'odd'): static
    {

        return parent::make($name)
            ->label('Chi tiết lẻ')
            ->color('warning')
            ->icon('ti ti-eye')
            ->attributes([
                'id' => 'detail_plan_material',
            ]);
    }
}
