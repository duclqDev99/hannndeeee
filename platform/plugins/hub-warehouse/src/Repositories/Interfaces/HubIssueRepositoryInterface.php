<?php

namespace Botble\HubWarehouse\Repositories\Interfaces;

interface HubIssueRepositoryInterface
{
    public function create(array $data);
    public function confirmReceiptInTour($data);
}
