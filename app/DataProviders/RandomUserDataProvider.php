<?php

namespace App\DataProviders;

use App\Contracts\CustomerDataProviderInterface;
use Illuminate\Support\Facades\Http;

class RandomUserDataProvider implements CustomerDataProviderInterface
{
    public function fetchCustomers(int $page = 1, int $resultsPerPage = 50): array
    {
        $response = Http::get('https://randomuser.me/api/', [
            'results' => $resultsPerPage,
            'page' => $page,
            'nat' => 'AU',
        ]);

        return $response->json()['results'];
    }
}

