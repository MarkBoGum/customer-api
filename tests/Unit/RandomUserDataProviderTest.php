<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\DataProviders\RandomUserDataProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mockery;

class RandomUserDataProviderTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testFetchCustomersSuccess()
    {
        // Mock the Http facade to return a successful response with 50 customers
        Http::fake([
            config('services.random_user_api.url') . '?' . http_build_query([
                'results' => 50,
                'page' => 1,
                'nat' => config('services.random_user_api.nationality'),
            ]) => Http::response([
                'results' => array_fill(0, 50, [
                    'name' => ['first' => 'John', 'last' => 'Doe'],
                    'email' => 'john.doe@example.com',
                    'login' => ['username' => 'johndoe', 'password' => 'password123'],
                    'gender' => 'male',
                    'location' => ['country' => 'Australia', 'city' => 'Sydney'],
                    'phone' => '1234567890',
                ]),
            ], 200)
        ]);

        $dataProvider = new RandomUserDataProvider();
        $customers = $dataProvider->fetchCustomers(1, 50);

        $this->assertIsArray($customers);
        $this->assertCount(50, $customers);
        $this->assertEquals('john.doe@example.com', $customers[0]['email']);
    }

    public function testFetchCustomersHandlesRequestException()
    {
        // Mock the Http facade to throw a RequestException
        Http::fake([
            config('services.random_user_api.url') . '?' . http_build_query([
                'results' => 50,
                'page' => 1,
                'nat' => config('services.random_user_api.nationality'),
            ]) => Http::response([], 500)
        ]);

        Log::shouldReceive('error')->once()->with(Mockery::on(function ($message) {
            return strpos($message, 'Failed to fetch customers') !== false;
        }));

        $dataProvider = new RandomUserDataProvider();
        $customers = $dataProvider->fetchCustomers(1, 50);

        $this->assertIsArray($customers);
        $this->assertEmpty($customers);
    }
}
