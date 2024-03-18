<?php

namespace Botble\Agent\Actions;

use Botble\Table\Actions\Action;

class OrderDetailAction extends Action
{
    public static function make(string $name = 'orderDetai'): static
    {
        return parent::make($name)
            ->label(trans(' Chi tiết'))
            ->color('primary')
            ->icon('ti ti-eye');
    }
}
