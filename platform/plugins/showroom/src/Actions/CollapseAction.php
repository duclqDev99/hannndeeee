<?php

namespace Botble\Showroom\Actions;

use Botble\Table\Actions\Action;

class CollapseAction extends Action
{
    public static function make(string $name = 'collapseButton'): static
    {
        return parent::make($name)
            ->label(trans('Chi tiết sản phẩm'))
            ->color('secondary')
            ->attributes([
                'class' => 'see-detail',
            ])
            ->icon('fa-solid fa-caret-down');
    }
}
