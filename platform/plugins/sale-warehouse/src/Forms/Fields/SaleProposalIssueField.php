<?php

namespace Botble\SaleWarehouse\Forms\Fields;

use Botble\Base\Forms\FormField;

class SaleProposalIssueField extends FormField
{
    protected function getTemplate(): string
    {
        return 'plugins/sale-warehouse::custom-fields.sale-proposal-issue';
    }
}
