<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\DataProviders\RandomUserDataProvider;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\Client\RequestException;
use Mockery;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

class RandomUserDataProviderTest extends TestCase
{
    protected $httpClientMock;
    protected $loggerMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClientMock = Mockery::mock(HttpClient::class);
        $this->loggerMock = Mockery::mock(LoggerInterface::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testFetchCustomersSuccess()
    {
        $apiUrl = config('services.random_user_api.url');
        $nationality = config('services.random_user_api.nationality');

        // Mock the HttpClient to return a successful response with 50 customers
        $this->httpClientMock
            ->shouldReceive('get')
            ->once()
            ->with($apiUrl, [
                'results' => 50,
                'page' => 1,
                'nat' => $nationality,
            ])
            ->andReturnSelf();

        $this->httpClientMock
            ->shouldReceive('throw')
            ->once()
            ->andReturnSelf();

        $this->httpClientMock
            ->shouldReceive('json')
            ->once()
            ->andReturn([
                'results' => array_fill(0, 50, [
                    'name' => ['first' => 'John', 'last' => 'Doe'],
                    'email' => 'john.doe@example.com',
                    'login' => ['username' => 'johndoe', 'password' => 'password123'],
                    'gender' => 'male',
                    'location' => ['country' => 'Australia', 'city' => 'Sydney'],
                    'phone' => '1234567890',
                ]),
            ]);

        $dataProvider = new RandomUserDataProvider($this->httpClientMock, $this->loggerMock);
        $customers = $dataProvider->fetchCustomers(1, 50);

        $this->assertIsArray($customers);
        $this->assertCount(50, $customers);
        $this->assertEquals('john.doe@example.com', $customers[0]['email']);
    }

    public function testFetchCustomersHandlesRequestException()
    {
        $apiUrl = config('services.random_user_api.url');
        $nationality = config('services.random_user_api.nationality');

        // Mock the Response and PsrResponse objects
        $responseMock = Mockery::mock('Illuminate\Http\Client\Response');
        $psrResponseMock = Mockery::mock(MessageInterface::class);
        $streamMock = Mockery::mock(StreamInterface::class);

        $responseMock->shouldReceive('status')->andReturn(500);
        $responseMock->shouldReceive('toPsrResponse')->andReturn($psrResponseMock);

        // Mock the PsrResponse to return a stream mock
        $psrResponseMock->shouldReceive('getBody')->andReturn($streamMock);

        // Mock the Stream to return a readable and seekable state
        $streamMock->shouldReceive('isSeekable')->andReturn(true);
        $streamMock->shouldReceive('isReadable')->andReturn(true);
        $streamMock->shouldReceive('getSize')->andReturn(0); // Simulate an empty body

        // Mock the HttpClient to throw a RequestException
        $this->httpClientMock
            ->shouldReceive('get')
            ->once()
            ->with($apiUrl, [
                'results' => 50,
                'page' => 1,
                'nat' => $nationality,
            ])
            ->andThrow(new RequestException($responseMock));

        // Expect the logger to receive an error message
        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->with(Mockery::on(function ($message) {
                return strpos($message, 'Failed to fetch customers') !== false;
            }));

        $dataProvider = new RandomUserDataProvider($this->httpClientMock, $this->loggerMock);
        $customers = $dataProvider->fetchCustomers(1, 50);

        $this->assertIsArray($customers);
        $this->assertEmpty($customers);
    }
}
