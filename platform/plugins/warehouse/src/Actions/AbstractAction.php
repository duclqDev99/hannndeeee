<?php

namespace Botble\Warehouse\Actions;

abstract class AbstractAction
{
    protected function error(string|null $message = null, array $data = null): array
    {
        if (! $message) {
            $message = trans('plugins/golf-caddie::base.error_occurred');
        }

        return response_with_messages($message, true, 500, $data);
    }

    protected function success(string|null $message = null, array $data = null): array
    {
        if (! $message) {
            $message = trans('Thành công');
        }

        return response_with_messages(
            $message,
            false,
            ! $data ? 200 : 201,
            $data
        );
    }
}
