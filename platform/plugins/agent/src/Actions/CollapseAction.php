<?php

namespace Botble\Agent\Actions;

use Botble\Table\Actions\Action;

class CollapseAction extends Action
{
    public static function make(string $name = 'collapseButton'): static
    {
        return parent::make($name)
            ->label(trans('xem sản phẩm'))
            ->color('secondary')
            ->icon('fa-solid fa-caret-down')
            ->attributes([
                'class' => 'see-detail',
            ]);
    }
}
