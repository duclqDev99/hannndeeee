<?php

namespace Botble\WidgetCustom\Facades;

use Botble\WidgetCustom\WidgetGroup;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Botble\WidgetCustom\Factories\WidgetFactory registerWidget(string $widget)
 * @method static array getWidgets()
 * @method static \Illuminate\Support\HtmlString|string|null run()
 *
 * @see \Botble\WidgetCustom\Factories\WidgetFactory
 */
class Widget extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'botble.widget-custom';
    }

    public static function group(string $name): WidgetGroup
    {
        return app('botble.widget-custom-group-collection')->group($name);
    }
}
