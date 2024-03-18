<?php

namespace Botble\Showroom\Actions;

use Botble\Table\Actions\Action;

class DetailAction extends Action
{
    public static function make(string $name = 'detail'): static
    {
        return parent::make($name)
            ->label(trans('Sản phẩm lô'))
            ->color('info')
            ->icon('ti ti-eye');
    }
}
