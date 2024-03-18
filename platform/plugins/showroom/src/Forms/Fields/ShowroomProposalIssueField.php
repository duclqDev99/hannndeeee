<?php

namespace Botble\Showroom\Forms\Fields;

use Botble\Base\Forms\FormField;

class ShowroomProposalIssueField extends FormField
{
    protected function getTemplate(): string
    {
        return 'plugins/showroom::custom-fields.showroom-proposal-issue';
    }
}
