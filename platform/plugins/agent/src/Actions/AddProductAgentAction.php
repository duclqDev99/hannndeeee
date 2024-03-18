<?php

namespace Botble\Agent\Actions;

use Botble\Table\Actions\Action;

class AddProductAgentAction extends Action
{
    public static function make(string $name = 'addProduct'): static
    {
        return parent::make($name)
            ->label(trans('Thêm sản phẩm'))
            ->color('success')
            ->icon('fa-solid fa-plus');
    }
}
