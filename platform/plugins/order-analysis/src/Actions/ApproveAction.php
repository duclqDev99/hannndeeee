<?php

namespace Botble\OrderAnalysis\Actions;

use Botble\Table\Actions\Action;

class ApproveAction extends Action
{
    public static function make(string $name = 'approve'): static
    {
        return parent::make($name)
            ->label(trans('core/base::tables.edit'))
            ->color('success')
            ->icon('ti ti-edit');
    }
}
