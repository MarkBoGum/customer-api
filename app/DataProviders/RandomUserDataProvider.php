<?php

namespace App\DataProviders;

use App\Contracts\CustomerDataProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;

class RandomUserDataProvider implements CustomerDataProviderInterface
{
    public function fetchCustomers(int $page = 1, int $resultsPerPage = 50): array
    {
        try {
            $response = Http::get(config('services.random_user_api.url'), [
                'results' => $resultsPerPage,
                'page' => $page,
                'nat' => config('services.random_user_api.nationality'),
            ]);

            $response->throw();

            return $response->json()['results'];
        } catch (RequestException $e) {
            Log::error("Failed to fetch customers: " . $e->getMessage());
            return [];
        }
    }
}
