<?php

namespace Botble\SaleWarehouse\Repositories\Interfaces;

interface SaleIssueRepositoryInterface
{
    public function create(array $data);
    public function confirmReceiptInTour($data);
}
