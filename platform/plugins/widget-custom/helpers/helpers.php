<?php

use Botble\WidgetCustom\Facades\Widget;
use Botble\WidgetCustom\Facades\WidgetGroup;
use Botble\WidgetCustom\Factories\WidgetFactory;
use Botble\WidgetCustom\WidgetGroupCollection;

if (! function_exists('register_widget_custom')) {
    function register_widget_custom(string $widgetId): WidgetFactory
    {
        return Widget::registerWidget($widgetId);
    }
}

if (! function_exists('register_sidebar')) {
    function register_sidebar(array $args): WidgetGroupCollection
    {
        return WidgetGroup::setGroup($args);
    }
}

if (! function_exists('remove_sidebar')) {
    function remove_sidebar(string $sidebarId): WidgetGroupCollection
    {
        return WidgetGroup::removeGroup($sidebarId);
    }
}

if (! function_exists('dynamic_sidebar_custom')) {
    function dynamic_sidebar_custom(string $sidebarId): string
    {
        return WidgetGroup::render($sidebarId);
    }
}
