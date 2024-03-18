<?php

namespace Botble\Sales\Tables\Actions;

class PrevStepAction extends Action
{
    public static function make(string $name = 'prev_step'): static
    {
        return parent::make($name)
            ->label(__('Quay về công đoạn trước'))
            ->color('info')
            ->icon('fa-solid fa-right-long')
            ->attributes([
                'class' => 'btn btn-warning prev_step_btn',
                'style' => "transform: rotate(180deg);"
            ]);
    }
}
