<?php

namespace Tests\Feature;

use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
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
}
