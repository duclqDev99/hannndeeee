<?php

namespace Botble\HubWarehouse\Actions;

use Botble\Table\Actions\Action;

class DetailHub extends Action
{

    public static function make(string $name = 'add'): static
    {

        return parent::make($name)
            ->label('Chi tiết hub')
            ->color('warning')
            ->icon('ti ti-info-circle')
            ->attributes([
                'id' => 'detail_plan_material',
            ]);
    }
}
