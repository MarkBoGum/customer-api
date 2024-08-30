<?php

namespace App\DataProviders;

use App\Contracts\CustomerDataProviderInterface;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\Client\RequestException;
use Psr\Log\LoggerInterface;

class RandomUserDataProvider implements CustomerDataProviderInterface
{
    protected $http;
    protected $logger;

    public function __construct(HttpClient $http, LoggerInterface $logger)
    {
        $this->http = $http;
        $this->logger = $logger;
    }

    public function fetchCustomers(int $page = 1, int $resultsPerPage = 50): array
    {
        try {
            $response = $this->http->get(config('services.random_user_api.url'), [
                'results' => $resultsPerPage,
                'page' => $page,
                'nat' => config('services.random_user_api.nationality'),
            ]);

            $response->throw();

            return $response->json()['results'];
        } catch (RequestException $e) {
            $this->logger->error("Failed to fetch customers: " . $e->getMessage());
            return [];
        }
    }
}
