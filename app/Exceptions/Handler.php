<?php

namespace App\Exceptions;

use ArchiElite\NotificationPlus\Facades\NotificationPlus;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use ArchiElite\NotificationPlus\Drivers\Telegram;
use Botble\Support\Http\Requests\Request;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram as TelegramBot;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (Throwable $e) {
            if(setting('tele_chat_id_error') != null) {
                TelegramBot::sendMessage([
                    'chat_id' => setting('tele_chat_id_error'),
                    'parse_mode' => 'HTML',
                    'text' => $e->getMessage() . " | " . url()->current() . " | IP:" . request()->ip(),
                ]);
            }
        });


    }
}
