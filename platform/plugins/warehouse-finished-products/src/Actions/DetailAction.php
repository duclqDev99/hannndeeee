<?php

namespace Botble\WarehouseFinishedProducts\Actions;

use Botble\Table\Actions\Action;

class DetailAction extends Action
{

    public static function make(string $name = 'detail'): static
    {
        return parent::make($name)
            ->label('Chi tiết')
            ->color('info')
            ->icon('fa-solid fa-info')
            ->attributes([
                'class' => 'accept_proposal_receipt_product',
            ]);
    }
}
