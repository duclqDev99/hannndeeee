<?php

namespace Botble\Agent\Actions;

use Botble\Table\Actions\Action;

class DetailNotQrAction extends Action
{
    public static function make(string $name = 'detailNotQr'): static
    {
        return parent::make($name)
            ->label(trans(' sản phẩm lẻ'))
            ->color('primary')
            ->icon('ti ti-eye');
    }
}
