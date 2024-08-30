<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Repositories\CustomerRepository;
use Mockery;
use Psr\Log\LoggerInterface;

class CustomerControllerTest extends TestCase
{
    protected $loggerMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loggerMock = Mockery::mock(LoggerInterface::class);
        $this->app->instance(LoggerInterface::class, $this->loggerMock);
    }

    public function testGetCustomers()
    {
        // Test retrieving the first 5 customers
        $response = $this->get('/api/customers?page=1&pageSize=5');
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data' => [
                         '*' => [
                             'full_name',
                             'email',
                             'country',
                         ]
                     ]
                 ]);

        // Test with invalid pageSize (above max)
        $response = $this->get('/api/customers?page=1&pageSize=6000');
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data' => [
                         '*' => [
                             'full_name',
                             'email',
                             'country',
                         ]
                     ]
                 ]);

        // Test with invalid page
        $response = $this->get('/api/customers?page=-1&pageSize=5');
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data' => [
                         '*' => [
                             'full_name',
                             'email',
                             'country',
                         ]
                     ]
                 ]);
    }

    public function testGetCustomersHandlesException()
    {
        // Mock the CustomerRepository to throw an exception
        $mock = Mockery::mock(CustomerRepository::class);
        $mock->shouldReceive('findAllCustomers')->andThrow(new \Exception('Database error'));

        $this->app->instance(CustomerRepository::class, $mock);

        $this->loggerMock->shouldReceive('error')->once()->with('Failed to retrieve customers: Database error');

        $response = $this->get('/api/customers');
        $response->assertStatus(500)
                 ->assertJson([
                     'message' => 'Unable to retrieve customers',
                 ]);
    }

    public function testGetCustomerDetails()
    {
        // Test existing customer
        $response = $this->get('/api/customers/1');
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data' => [
                         'full_name',
                         'email',
                         'username',
                         'gender',
                         'country',
                         'city',
                         'phone',
                     ]
                 ]);

        // Test invalid customer ID (non-integer)
        $response = $this->get('/api/customers/abc');
        $response->assertStatus(400)
                 ->assertJson([
                     'message' => 'Invalid customer ID',
                 ]);

        // Test non-existing customer
        $response = $this->get('/api/customers/999');
        $response->assertStatus(404)
                 ->assertJson([
                     'message' => 'Customer not found',
                 ]);
    }

    public function testGetCustomerDetailsHandlesException()
    {
        // Mock the CustomerRepository to throw an exception
        $mock = Mockery::mock(CustomerRepository::class);
        $mock->shouldReceive('findCustomerById')->andThrow(new \Exception('Database error'));

        $this->app->instance(CustomerRepository::class, $mock);

        $this->loggerMock->shouldReceive('error')->once()->with('Failed to retrieve customer: Database error');

        $response = $this->get('/api/customers/1');
        $response->assertStatus(500)
                 ->assertJson([
                     'message' => 'Unable to retrieve customer',
                 ]);
    }
}
