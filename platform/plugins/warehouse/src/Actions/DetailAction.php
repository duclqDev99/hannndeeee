<?php

namespace Botble\Warehouse\Actions;

use Botble\Table\Actions\Action;

class DetailAction extends Action
{

    public static function make(string $name = 'add'): static
    {

        return parent::make($name)
            ->label('Chi tiết lô')
            ->color('info')
            ->icon('fa fa-info-circle')
            ->attributes([
                'id' => 'detail_plan_material',
            ]);
    }
}
