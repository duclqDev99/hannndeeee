<?php

namespace Botble\Showroom\Actions;

use Botble\Table\Actions\Action;

class DetailNotQrAction extends Action
{
    public static function make(string $name = 'detailNotQr'): static
    {
        return parent::make($name)
            ->label(trans('Sản phẩm lẻ'))
            ->color('info')
            ->icon('ti ti-eye');
    }
}
