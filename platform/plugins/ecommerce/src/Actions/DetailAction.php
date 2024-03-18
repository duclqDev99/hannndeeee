<?php

namespace Botble\Ecommerce\Actions;
use Botble\Table\Actions\Action;
class DetailAction extends Action
{
    public static function make(string $name = 'detail'): static
    {
        return parent::make($name)
            ->label(trans('Chi tiết'))
            ->color('warning')
            ->icon('ti ti-eye');
    }
}
