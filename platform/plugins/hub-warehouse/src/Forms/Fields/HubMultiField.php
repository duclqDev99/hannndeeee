<?php

namespace Botble\HubWarehouse\Forms\Fields;

use Botble\Base\Forms\FormField;

class HubMultiField extends FormField
{
    protected function getTemplate(): string
    {
        return 'plugins/hub-warehouse::custom-fields.proposal-hub-receipt';
    }
}
