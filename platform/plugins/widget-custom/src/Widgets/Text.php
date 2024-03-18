<?php

namespace Botble\WidgetCustom\Widgets;

use Botble\WidgetCustom\AbstractWidget;

class Text extends AbstractWidget
{
    public function __construct()
    {
        parent::__construct([
            'name' => trans('packages/widget::widget.widget_text'),
            'description' => trans('packages/widget::widget.widget_text_description'),
            'content' => null,
        ]);

        $widgetDirectory = $this->getWidgetDirectory();

        $this->setFrontendTemplate('plugins/widget-custom::widgets.' . $widgetDirectory . '.frontend');
        $this->setBackendTemplate('plugins/widget-custom::widgets.' . $widgetDirectory . '.backend');
    }

    public function getWidgetDirectory(): string
    {
        return 'text';
    }
}
