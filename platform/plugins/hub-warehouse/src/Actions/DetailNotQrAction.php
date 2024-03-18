<?php

namespace Botble\HubWarehouse\Actions;

use Botble\Table\Actions\Action;

class DetailNotQrAction extends Action
{
    public static function make(string $name = 'detailNotQr'): static
    {
        return parent::make($name)
            ->label(trans('Chi tiết sản phẩm lẻ'))
            ->color('warning')
            ->icon('fa-solid fa-brush');
    }
}
