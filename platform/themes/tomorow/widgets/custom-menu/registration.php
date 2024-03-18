<?php


if(is_plugin_active('widget-custom')){
    require_once __DIR__ . '/custom-menu.php';

    register_widget_custom(CustomMenuWidget::class);
}
