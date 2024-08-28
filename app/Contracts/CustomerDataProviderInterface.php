<?php

namespace App\Contracts;

interface CustomerDataProviderInterface
{
    public function fetchCustomers(int $page, int $pageSize): array;
}
