<?php

namespace Botble\Showroom\Actions;

use Botble\Table\Actions\Action;

class ReduceQuantityAction extends Action
{
    public static function make(string $name = 'redureQuantity'): static
    {
        return parent::make($name)
            ->label(trans('giảm số lượng'))
            ->color('primary')
            ->icon('fa-regular fa-square-minus')
            ->action('DELETE')
            ->confirmationModalTitle(trans('core/base::tables.confirm_delete'))
            ->confirmationModalMessage(trans('core/base::tables.confirm_delete_msg'))
            ->confirmationModalButton(trans('core/base::tables.delete'));
    }
}
