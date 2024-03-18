<?php

namespace Botble\Showroom\Actions;

use Botble\Table\Actions\Action;

class DetailActionProduct extends Action
{
    public static function make(string $name = 'detailProduct'): static
    {
        return parent::make($name)
            ->label(trans('Sản phẩm lẻ'))
            ->color('warning')
            ->icon('ti ti-eye');
    }
}
