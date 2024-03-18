<?php

namespace Botble\Showroom\Forms\Fields;

use Botble\Base\Forms\FormField;

class ProposalShowroomReceiptField extends FormField
{
    protected function getTemplate(): string
    {
        return 'plugins/showroom::custom-fields.proposal-showroom-receipt';
    }
}
