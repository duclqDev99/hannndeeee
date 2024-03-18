<?php

namespace Botble\SharedModule\Supports;

use ArchiElite\EcommerceNotification\Supports\EcommerceNotification;
use ArchiElite\NotificationPlus\Facades\NotificationPlus;
use Botble\Base\Supports\TwigCompiler;
use Botble\Ecommerce\Supports\TwigExtension;
use Botble\SharedModule\Drivers\MyTeleNoti;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Spatie\LaravelIgnition\Recorders\DumpRecorder\DumpHandler;

class MyEcommerceNotification extends EcommerceNotification
{
    public static function make(): self
    {
        return new self();
    }

    public function sendNotifyToDriversUsing(string $key, string $message, array $customVariables = []): void
    {
        $twigCompiler = new TwigCompiler();
        $twigCompiler->addExtension(new TwigExtension());

        $data = [];

        if (! empty($customVariables)) {
            $this->variables = array_merge($this->variables, $customVariables);
        }

        $message = $twigCompiler->compile($message, $this->variables);

        $this->variables['subject'] = $message;
        foreach (NotificationPlus::getAvailableDrivers() as $driver) {
            $driver = NotificationPlus::driver($driver);
            $name = $driver->getShortName();
            
            if (File::exists($path = __DIR__ . "/../../resources/templates/$name/$key.tpl")) {
                $content = File::get($path);
                if ($content) {
                    $content = $twigCompiler->compile($content, $this->variables);
                    $data = json_decode($content, true);

                    if (! $data) {
                        $message = $content;
                    }
                }
            }
            if(array_key_exists('chat_id',$customVariables)){
                if (method_exists($driver, 'setChatId')) {
                    $driver->setChatId($customVariables['chat_id']);
                    $driver->send($message, (array) $data);
                }
            }
        }
    }
}
