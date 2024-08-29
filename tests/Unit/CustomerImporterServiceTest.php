<?php

namespace Tests\Unit;

use Mockery;
use PHPUnit\Framework\TestCase;
use App\Contracts\CustomerDataProviderInterface;
use App\Repositories\CustomerRepository;
use App\Services\CustomerImporterService;
use App\Entities\Customer;

class CustomerImporterServiceTest extends TestCase
{
    protected $dataProviderMock;
    protected $customerRepositoryMock;
    protected $importerService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dataProviderMock = Mockery::mock(CustomerDataProviderInterface::class);
        $this->customerRepositoryMock = Mockery::mock(CustomerRepository::class);

        $this->importerService = new CustomerImporterService(
            $this->dataProviderMock,
            $this->customerRepositoryMock
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testImportCustomers()
    {
        $mockData = [
            [
                "gender" => "male",
                "name" => [
                    "first" => "Warren",
                    "last" => "Byrd",
                ],
                "location" => [
                    "city" => "Perth",
                    "country" => "Australia",
                ],
                "email" => "warren.byrd@example.com",
                "login" => [
                    "username" => "tinyfish792",
                    "password" => "tacobell",
                ],
                "phone" => "05-0360-2392",
            ]
        ];

        $this->dataProviderMock
            ->shouldReceive('fetchCustomers')
            ->with(1, 1)
            ->andReturn($mockData);

        $this->customerRepositoryMock
            ->shouldReceive('findByEmail')
            ->andReturn(null); // Simulate no existing customer

        $this->customerRepositoryMock
            ->shouldReceive('save')
            ->andReturnUsing(function ($customer) use ($mockData) {
                $this->assertEquals($mockData[0]['email'], $customer->getEmail());
                $this->assertEquals($mockData[0]['name']['first'], $customer->getFirstName());
                $this->assertEquals($mockData[0]['name']['last'], $customer->getLastName());
                $this->assertEquals($mockData[0]['login']['username'], $customer->getUsername());
                $this->assertEquals($mockData[0]['gender'], $customer->getGender());
                $this->assertEquals($mockData[0]['location']['country'], $customer->getCountry());
                $this->assertEquals($mockData[0]['location']['city'], $customer->getCity());
                $this->assertEquals($mockData[0]['phone'], $customer->getPhone());
                $this->assertEquals(md5($mockData[0]['login']['password']), $customer->getPassword());
            });

        $this->customerRepositoryMock
            ->shouldReceive('flush')
            ->andReturn(null);

        $this->importerService->importCustomers(1);

        $this->customerRepositoryMock->shouldHaveReceived('save')->once();
        $this->customerRepositoryMock->shouldHaveReceived('flush')->once();
    }

    public function testImportExistingCustomer()
    {
        $existingCustomer = new Customer();
        $existingCustomer->setFirstName("Existing");
        $existingCustomer->setLastName("Customer");
        $existingCustomer->setEmail("customer1@example.com");
        $existingCustomer->setUsername("existingusername");
        $existingCustomer->setGender("female");
        $existingCustomer->setCountry("Australia");
        $existingCustomer->setCity("Melbourne");
        $existingCustomer->setPhone("987654322");
        $existingCustomer->setPassword(md5('securepassword'));
        $existingCustomer->setCreatedAt(new \DateTime());
        $existingCustomer->setUpdatedAt(new \DateTime());

        $mockData = [
            [
                "gender" => "female",
                "name" => [
                    "first" => "Existing",
                    "last" => "Customer",
                ],
                "location" => [
                    "city" => "Melbourne",
                    "country" => "Australia",
                ],
                "email" => "customer1@example.com",
                "login" => [
                    "username" => "existingusername",
                    "password" => "securepassword",
                ],
                "phone" => "987654322",
            ]
        ];

        $this->dataProviderMock
            ->shouldReceive('fetchCustomers')
            ->with(1, 1)
            ->andReturn($mockData);

        $this->customerRepositoryMock
            ->shouldReceive('findByEmail')
            ->andReturn($existingCustomer); // Simulate existing customer

        $this->customerRepositoryMock
            ->shouldReceive('save')
            ->andReturnUsing(function ($customer) use ($mockData) {
                $this->assertEquals($mockData[0]['email'], $customer->getEmail());
                $this->assertEquals($mockData[0]['name']['first'], $customer->getFirstName());
                $this->assertEquals($mockData[0]['name']['last'], $customer->getLastName());
                $this->assertEquals($mockData[0]['login']['username'], $customer->getUsername());
                $this->assertEquals($mockData[0]['gender'], $customer->getGender());
                $this->assertEquals($mockData[0]['location']['country'], $customer->getCountry());
                $this->assertEquals($mockData[0]['location']['city'], $customer->getCity());
                $this->assertEquals($mockData[0]['phone'], $customer->getPhone());
                $this->assertEquals(md5($mockData[0]['login']['password']), $customer->getPassword());
            });

        $this->customerRepositoryMock
            ->shouldReceive('flush')
            ->andReturn(null);

        $this->importerService->importCustomers(1);

        $this->customerRepositoryMock->shouldHaveReceived('save')->once();
        $this->customerRepositoryMock->shouldHaveReceived('flush')->once();
    }

    public function testImportCustomersWithBatchFlush()
    {
        $batchSize = 20; // Defined in the service class
        $mockData = [];

        // Generate mock data to match the batch size
        for ($i = 1; $i <= $batchSize; $i++) {
            $mockData[] = [
                "gender" => "male",
                "name" => ["first" => "First$i", "last" => "Last$i"],
                "location" => [
                    "city" => "City$i",
                    "country" => "Australia",
                ],
                "email" => "customer$i@example.com",
                "login" => [
                    "username" => "user$i",
                    "password" => "password$i",
                ],
                "phone" => "123456789$i",
            ];
        }

        // Mock the fetchCustomers method to return the batchSize amount of data
        $this->dataProviderMock
            ->shouldReceive('fetchCustomers')
            ->with(1, $batchSize)
            ->andReturn($mockData);

        $this->customerRepositoryMock
            ->shouldReceive('findByEmail')
            ->andReturn(null);

        $this->customerRepositoryMock
            ->shouldReceive('save')
            ->times($batchSize)
            ->andReturnUsing(function ($customer) use ($mockData) {
                // Extract the index from the email correctly
                $index = (int) filter_var($customer->getEmail(), FILTER_SANITIZE_NUMBER_INT) - 1;
                $this->assertEquals($mockData[$index]['email'], $customer->getEmail());
                $this->assertEquals($mockData[$index]['name']['first'], $customer->getFirstName());
                $this->assertEquals($mockData[$index]['name']['last'], $customer->getLastName());
            });

        $this->customerRepositoryMock
            ->shouldReceive('flush')
            ->once();

        $this->importerService->importCustomers($batchSize);

        $this->customerRepositoryMock->shouldHaveReceived('save')->times($batchSize);
        $this->customerRepositoryMock->shouldHaveReceived('flush')->once();
    }
}
