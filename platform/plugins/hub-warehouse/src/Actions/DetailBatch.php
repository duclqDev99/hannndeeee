<?php

namespace Botble\HubWarehouse\Actions;

use Botble\Table\Actions\Action;

class DetailBatch extends Action
{

    public static function make(string $name = 'add'): static
    {

        return parent::make($name)
            ->label('Chi tiết lô')
            ->color('info')
            ->icon('ti ti-info-circle')
            ->attributes([
                'id' => 'detail_plan_material',
            ]);
    }
}
