<?php

namespace Botble\Agent\Actions;

use Botble\Table\Actions\Action;

class DetailAction extends Action
{
    public static function make(string $name = 'detail'): static
    {
        return parent::make($name)
            ->label(trans(' sản phẩm lô'))
            ->color('primary')
            ->icon('ti ti-eye');
    }
}
