<?php

namespace Botble\Sales\Tables\Actions;

class NextStepAction extends Action
{
    public static function make(string $name = 'next_step'): static
    {  
        return parent::make($name)
            ->label(__('Chuyển tiếp công đoạn'))
            ->color('success')
            ->icon('fa-solid fa-right-long')
            ->attributes([
                'class' => 'btn btn-success next_step_btn',
            ]);
    }
}
