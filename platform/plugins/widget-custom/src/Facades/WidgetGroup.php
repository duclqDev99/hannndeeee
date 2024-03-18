<?php

namespace Botble\WidgetCustom\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Botble\WidgetCustom\WidgetGroup group(string $sidebarId)
 * @method static \Botble\WidgetCustom\WidgetGroupCollection setGroup(array $args)
 * @method static \Botble\WidgetCustom\WidgetGroupCollection removeGroup(string $groupId)
 * @method static array getGroups()
 * @method static string render(string $sidebarId)
 * @method static void load(bool $force = false)
 * @method static \Illuminate\Support\Collection getData()
 *
 * @see \Botble\WidgetCustom\WidgetGroupCollection
 */
class WidgetGroup extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'botble.widget-custom-group-collection';
    }
}
