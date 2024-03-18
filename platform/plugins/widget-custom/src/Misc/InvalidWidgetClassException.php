<?php

namespace Botble\WidgetCustom\Misc;

use Botble\WidgetCustom\AbstractWidget;
use Exception;

class InvalidWidgetClassException extends Exception
{
    protected $message = 'Widget class must extend class ' . AbstractWidget::class;
}
