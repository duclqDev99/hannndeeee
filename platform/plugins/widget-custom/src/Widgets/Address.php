<?php

namespace Botble\WidgetCustom\Widgets;

use Botble\WidgetCustom\AbstractWidget;

class Address extends AbstractWidget
{
    public function __construct()
    {
        parent::__construct([
            'name' => __('Địa chỉ'),
            'description' => __('Thông tin địa chỉ công ty/chi nhánh'),
            'phone' => null,
        ]);

        $widgetDirectory = $this->getWidgetDirectory();

        $this->setFrontendTemplate('plugins/widget-custom::widgets.' . $widgetDirectory . '.frontend');
        $this->setBackendTemplate('plugins/widget-custom::widgets.' . $widgetDirectory . '.backend');
    }

    public function getWidgetDirectory(): string
    {
        return 'address';
    }
}
