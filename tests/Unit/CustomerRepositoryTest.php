<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Entities\Customer;
use App\Repositories\CustomerRepository;

class CustomerRepositoryTest extends TestCase
{
    protected $customerRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customerRepository = $this->app->make(CustomerRepository::class);
    }

    public function testFindByEmail()
    {
        // Test existing email from the fixture data
        $customer = $this->customerRepository->findByEmail('customer1@example.com');
        $this->assertNotNull($customer);
        $this->assertEquals('customer1@example.com', $customer->getEmail());

        // Test non-existing email
        $nonExistingCustomer = $this->customerRepository->findByEmail('nonexistent@example.com');
        $this->assertNull($nonExistingCustomer);
    }

    public function testSaveAndFlush()
    {
        // Create a new customer that isn't in the fixtures
        $customer = new Customer();
        $customer->setFirstName('Jane');
        $customer->setLastName('Doe');
        $customer->setEmail('jane.doe@example.com');
        $customer->setUsername('janedoe');
        $customer->setGender('female');
        $customer->setCountry('Australia');
        $customer->setCity('Melbourne');
        $customer->setPhone('987654322');
        $customer->setPassword(md5('securepassword'));
        $customer->setCreatedAt(new \DateTime());
        $customer->setUpdatedAt(new \DateTime());

        $this->customerRepository->save($customer);
        $this->customerRepository->flush();

        // Verify that the customer was saved correctly
        $savedCustomer = $this->customerRepository->findByEmail('jane.doe@example.com');
        $this->assertNotNull($savedCustomer);
        $this->assertEquals('jane.doe@example.com', $savedCustomer->getEmail());
        
        $this->assertNotNull($savedCustomer->getId());
        $this->assertInstanceOf(\DateTimeInterface::class, $savedCustomer->getCreatedAt());
        $this->assertInstanceOf(\DateTimeInterface::class, $savedCustomer->getUpdatedAt());
    }

    public function testFindAllCustomers()
    {
        $page = 1;
        $pageSize = 5;
        $customers = $this->customerRepository->findAllCustomers($page, $pageSize);

        $this->assertCount(5, $customers);
        $this->assertArrayHasKey('full_name', $customers[0]);
        $this->assertArrayHasKey('email', $customers[0]);
        $this->assertArrayHasKey('country', $customers[0]);
    }

    public function testFindCustomerById()
    {
        // Test existing customer
        $customerId = 1; // Assuming the first customer has ID 1
        $customer = $this->customerRepository->findCustomerById($customerId);
        $this->assertNotNull($customer);
        $this->assertArrayHasKey('full_name', $customer);
        $this->assertArrayHasKey('email', $customer);
        $this->assertArrayHasKey('username', $customer);

        // Test non-existing customer
        $nonExistingCustomer = $this->customerRepository->findCustomerById(999);
        $this->assertNull($nonExistingCustomer);
    }
}
