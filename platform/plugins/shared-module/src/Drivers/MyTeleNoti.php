<?php

namespace Botble\SharedModule\Drivers;

use ArchiElite\NotificationPlus\Drivers\Telegram;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class MyTeleNoti extends Telegram
{
    protected array $variables = [];
    public string $chatID = '';

    

    public function isEnabled():bool {
        return true;
    }
    public function send(string $message, array $data = []): array
    {
        if (! $this->isEnabled()) {
            return [
                'success' => false,
                'message' => 'Telegram is not enabled',
            ];
        }

        if (! setting('ae_notification_plus_archi-elite-notification-plus-drivers-telegram_bot_token')) {
            return [
                'success' => false,
                'message' => 'Bot token or chat id is not set',
            ];
        }

        $response = self::sendMessage($message);

        if (Arr::get($response, 'ok') !== true) {
            return [
                'success' => false,
                'message' => Arr::get($response, 'description'),
            ];
        }

        return [
            'success' => true,
        ];
    }

    protected function sendMessage(string $message): array
    {
        
        $response = $this->requestTele('POST', 'sendMessage', [
            'chat_id' => $this->chatID,
            'text' => $message,
            'parse_mode' => 'HTML',
        ]);

        if (! $response->json('ok')) {
            logger()->error($response->json());
        }

        return $response->json();
    }

    public function getChatId()
    {
        return $this->chatID;
    }

    public function setChatId(string $chatID)
    {
        $this->chatID = $chatID;
    }
    protected function requestTele(string $method, string $uri, array $data = [])
    {
        $botToken = setting('ae_notification_plus_archi-elite-notification-plus-drivers-telegram_bot_token');

        return match (strtoupper($method)) {
            'POST' => Http::post(self::API_URL . $botToken . '/' . $uri, $data),
            default => Http::get(self::API_URL . $botToken . '/' . $uri, $data),
        };
    }
}
