<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Transformers\RandomUserTransformer;

class RandomUserTransformerTest extends TestCase
{
    protected $transformer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transformer = new RandomUserTransformer();
    }

    public function testTransform()
    {
        $mockData = [
            'name' => [
                'first' => 'John',
                'last' => 'Doe',
            ],
            'email' => 'john.doe@example.com',
            'login' => [
                'username' => 'johndoe',
                'password' => 'password123',
            ],
            'gender' => 'male',
            'location' => [
                'country' => 'Australia',
                'city' => 'Sydney',
            ],
            'phone' => '1234567890',
        ];

        $expectedTransformedData = [
            'first_name' => 'John',
            'last_name'  => 'Doe',
            'email'      => 'john.doe@example.com',
            'username'   => 'johndoe',
            'gender'     => 'male',
            'country'    => 'Australia',
            'city'       => 'Sydney',
            'phone'      => '1234567890',
            'password'   => md5('password123'),
            'created_at' => new \DateTime(),
            'updated_at' => new \DateTime(),
        ];

        $transformedData = $this->transformer->transform($mockData);

        $this->assertEquals($expectedTransformedData['first_name'], $transformedData['first_name']);
        $this->assertEquals($expectedTransformedData['last_name'], $transformedData['last_name']);
        $this->assertEquals($expectedTransformedData['email'], $transformedData['email']);
        $this->assertEquals($expectedTransformedData['username'], $transformedData['username']);
        $this->assertEquals($expectedTransformedData['gender'], $transformedData['gender']);
        $this->assertEquals($expectedTransformedData['country'], $transformedData['country']);
        $this->assertEquals($expectedTransformedData['city'], $transformedData['city']);
        $this->assertEquals($expectedTransformedData['phone'], $transformedData['phone']);
        $this->assertEquals($expectedTransformedData['password'], $transformedData['password']);
        $this->assertInstanceOf(\DateTime::class, $transformedData['created_at']);
        $this->assertInstanceOf(\DateTime::class, $transformedData['updated_at']);
    }
}
