<?php

namespace Botble\Agent\Forms\Fields;

use Botble\Base\Forms\FormField;

class ProposalAgentIssueField extends FormField
{
    protected function getTemplate(): string
    {
        return 'plugins/agent::custom-fields.proposal-agent-issue';
    }
}
