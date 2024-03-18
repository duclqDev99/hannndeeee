<?php

namespace Botble\Agent\Actions;
use Botble\Table\Actions\Action;

class ConfirmAction extends Action
{
    public static function make(string $name = 'Xác nhận'): static
    {
        return parent::make($name)
            ->label(trans('Xác nhận'))
            ->color('success')
            ->icon('fa-solid fa-file-import');
    }
}
