<?php

namespace Tests\Unit;

use Mockery;
use PHPUnit\Framework\TestCase;
use App\Contracts\CustomerDataProviderInterface;
use App\Repositories\CustomerRepository;
use App\Services\CustomerImporterService;
use App\Entities\Customer;
use App\Transformers\RandomUserTransformer;
use Psr\Log\LoggerInterface;

class CustomerImporterServiceTest extends TestCase
{
    protected $dataProviderMock;
    protected $customerRepositoryMock;
    protected $randomUserTransformerMock;
    protected $loggerMock;
    protected $importerService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dataProviderMock = Mockery::mock(CustomerDataProviderInterface::class);
        $this->customerRepositoryMock = Mockery::mock(CustomerRepository::class);
        $this->randomUserTransformerMock = Mockery::mock(RandomUserTransformer::class);
        $this->loggerMock = Mockery::mock(LoggerInterface::class);

        $this->importerService = new CustomerImporterService(
            $this->dataProviderMock,
            $this->customerRepositoryMock,
            $this->randomUserTransformerMock,
            $this->loggerMock
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

        $transformedData = [
            'first_name' => 'Warren',
            'last_name' => 'Byrd',
            'email' => 'warren.byrd@example.com',
            'username' => 'tinyfish792',
            'gender' => 'male',
            'country' => 'Australia',
            'city' => 'Perth',
            'phone' => '05-0360-2392',
            'password' => md5('tacobell'),
            'created_at' => new \DateTime(),
            'updated_at' => new \DateTime(),
        ];

        $this->dataProviderMock
            ->shouldReceive('fetchCustomers')
            ->with(1, 1)
            ->andReturn($mockData);

        $this->randomUserTransformerMock
            ->shouldReceive('transform')
            ->with($mockData[0])
            ->andReturn($transformedData);

        $this->customerRepositoryMock
            ->shouldReceive('findByEmail')
            ->with('warren.byrd@example.com')
            ->andReturn(null); // Simulate no existing customer

        $this->customerRepositoryMock
            ->shouldReceive('save')
            ->andReturnUsing(function ($customer) use ($transformedData) {
                $this->assertEquals($transformedData['email'], $customer->getEmail());
                $this->assertEquals($transformedData['first_name'], $customer->getFirstName());
                $this->assertEquals($transformedData['last_name'], $customer->getLastName());
                $this->assertEquals($transformedData['username'], $customer->getUsername());
                $this->assertEquals($transformedData['gender'], $customer->getGender());
                $this->assertEquals($transformedData['country'], $customer->getCountry());
                $this->assertEquals($transformedData['city'], $customer->getCity());
                $this->assertEquals($transformedData['phone'], $customer->getPhone());
                $this->assertEquals($transformedData['password'], $customer->getPassword());
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

        $transformedData = [
            'first_name' => 'Existing',
            'last_name' => 'Customer',
            'email' => 'customer1@example.com',
            'username' => 'existingusername',
            'gender' => 'female',
            'country' => 'Australia',
            'city' => 'Melbourne',
            'phone' => '987654322',
            'password' => md5('securepassword'),
            'created_at' => new \DateTime(),
            'updated_at' => new \DateTime(),
        ];

        $this->dataProviderMock
            ->shouldReceive('fetchCustomers')
            ->with(1, 1)
            ->andReturn($mockData);

        $this->randomUserTransformerMock
            ->shouldReceive('transform')
            ->with($mockData[0])
            ->andReturn($transformedData);

        $this->customerRepositoryMock
            ->shouldReceive('findByEmail')
            ->with('customer1@example.com')
            ->andReturn($existingCustomer); // Simulate existing customer

        $this->customerRepositoryMock
            ->shouldReceive('save')
            ->andReturnUsing(function ($customer) use ($transformedData) {
                $this->assertEquals($transformedData['email'], $customer->getEmail());
                $this->assertEquals($transformedData['first_name'], $customer->getFirstName());
                $this->assertEquals($transformedData['last_name'], $customer->getLastName());
                $this->assertEquals($transformedData['username'], $customer->getUsername());
                $this->assertEquals($transformedData['gender'], $customer->getGender());
                $this->assertEquals($transformedData['country'], $customer->getCountry());
                $this->assertEquals($transformedData['city'], $customer->getCity());
                $this->assertEquals($transformedData['phone'], $customer->getPhone());
                $this->assertEquals($transformedData['password'], $customer->getPassword());
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

        $transformedData = [];
        foreach ($mockData as $i => $data) {
            $transformedData[] = [
                'first_name' => $data['name']['first'],
                'last_name' => $data['name']['last'],
                'email' => $data['email'],
                'username' => $data['login']['username'],
                'gender' => $data['gender'],
                'country' => $data['location']['country'],
                'city' => $data['location']['city'],
                'phone' => $data['phone'],
                'password' => md5($data['login']['password']),
                'created_at' => new \DateTime(),
                'updated_at' => new \DateTime(),
            ];
        }

        $this->dataProviderMock
            ->shouldReceive('fetchCustomers')
            ->with(1, $batchSize)
            ->andReturn($mockData);

        $this->randomUserTransformerMock
            ->shouldReceive('transform')
            ->andReturnUsing(function ($data) use ($transformedData) {
                return $transformedData[array_search($data['email'], array_column($transformedData, 'email'))];
            });

        $this->customerRepositoryMock
            ->shouldReceive('findByEmail')
            ->andReturn(null);

        $this->customerRepositoryMock
            ->shouldReceive('save')
            ->times($batchSize)
            ->andReturnUsing(function ($customer) use ($transformedData) {
                $index = (int) filter_var($customer->getEmail(), FILTER_SANITIZE_NUMBER_INT) - 1;
                $this->assertEquals($transformedData[$index]['email'], $customer->getEmail());
                $this->assertEquals($transformedData[$index]['first_name'], $customer->getFirstName());
                $this->assertEquals($transformedData[$index]['last_name'], $customer->getLastName());
            });

        $this->customerRepositoryMock
            ->shouldReceive('flush')
            ->once();

        $this->importerService->importCustomers($batchSize);

        $this->customerRepositoryMock->shouldHaveReceived('save')->times($batchSize);
        $this->customerRepositoryMock->shouldHaveReceived('flush')->once();
    }

    public function testImportCustomersHandlesException()
    {
        $this->dataProviderMock
            ->shouldReceive('fetchCustomers')
            ->andThrow(new \Exception('API error'));

        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->with('Failed to import customers: API error');

        $this->expectException(\Exception::class);

        $this->importerService->importCustomers(1);
    }
}
