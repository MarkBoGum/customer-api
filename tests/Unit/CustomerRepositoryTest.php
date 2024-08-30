<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Entities\Customer;
use App\Repositories\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Persisters\Entity\EntityPersister;
use Doctrine\ORM\Exception\ORMException;
use Mockery;
use Psr\Log\LoggerInterface;

class CustomerRepositoryTest extends TestCase
{
    protected $customerRepository;
    protected $loggerMock;
    protected $entityManagerMock;
    protected $classMetadataMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loggerMock = Mockery::mock(LoggerInterface::class);
        $this->entityManagerMock = Mockery::mock(EntityManagerInterface::class);
        $this->classMetadataMock = Mockery::mock(ClassMetadata::class);
        $this->classMetadataMock->name = Customer::class;

        // Mock the UnitOfWork and EntityPersister
        $unitOfWorkMock = Mockery::mock('Doctrine\ORM\UnitOfWork');
        $entityPersisterMock = Mockery::mock(EntityPersister::class);

        // Mocking the load method to return the customer object
        $entityPersisterMock->shouldReceive('load')
            ->with(['email' => 'customer1@example.com'], null, null, [], null, 1, null)
            ->andReturn((new Customer())->setEmail('customer1@example.com'));

        $entityPersisterMock->shouldReceive('load')
            ->with(['email' => 'nonexistent@example.com'], null, null, [], null, 1, null)
            ->andReturn(null);

        $unitOfWorkMock->shouldReceive('getEntityPersister')
            ->with(Customer::class)
            ->andReturn($entityPersisterMock);

        $this->entityManagerMock->shouldReceive('getUnitOfWork')
            ->andReturn($unitOfWorkMock);

        $this->customerRepository = new CustomerRepository($this->entityManagerMock, $this->classMetadataMock);
        $this->customerRepository->setLogger($this->loggerMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testFindByEmail()
    {
        $email = 'customer1@example.com';

        $result = $this->customerRepository->findByEmail($email);

        $this->assertNotNull($result);
        $this->assertEquals($email, $result->getEmail());

        // Test for a non-existing customer
        $nonExistingCustomer = $this->customerRepository->findByEmail('nonexistent@example.com');
        $this->assertNull($nonExistingCustomer);
    }

    public function testSaveAndFlush()
    {
        $customer = new Customer();
        $customer->setFirstName('Jane');
        $customer->setLastName('Doe');
        $customer->setEmail('jane.doe@example.com');

        $this->entityManagerMock
            ->shouldReceive('persist')
            ->once()
            ->with($customer);
        
        $this->entityManagerMock
            ->shouldReceive('flush')
            ->once();

        $this->customerRepository->save($customer);
        $this->customerRepository->flush();

        $this->entityManagerMock->shouldHaveReceived('persist')->with($customer)->once();
        $this->entityManagerMock->shouldHaveReceived('flush')->once();
        
        $this->addToAssertionCount(1);
    }

    public function testSaveHandlesException()
    {
        $customer = new Customer();
        $customer->setFirstName('Jane');
        $customer->setLastName('Doe');
        $customer->setEmail('jane.doe@example.com');

        $this->entityManagerMock
            ->shouldReceive('persist')
            ->andThrow(new ORMException('Error saving customer'));

        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->with('Failed to save customer: Error saving customer');

        $this->expectException(ORMException::class);

        $this->customerRepository->save($customer);
    }

    public function testFlushHandlesException()
    {
        $this->entityManagerMock
            ->shouldReceive('flush')
            ->andThrow(new ORMException('Error flushing changes'));

        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->with('Failed to flush customer changes: Error flushing changes');

        $this->expectException(ORMException::class);

        $this->customerRepository->flush();
    }

    public function testFindAllCustomers()
    {
        $page = 1;
        $pageSize = 5;

        $queryMock = Mockery::mock(AbstractQuery::class)
            ->shouldReceive('setFirstResult')
            ->andReturnSelf()
            ->shouldReceive('setMaxResults')
            ->andReturnSelf()
            ->shouldReceive('getArrayResult')
            ->andReturn([
                [
                    'full_name' => 'John Doe',
                    'email' => 'john.doe@example.com',
                    'country' => 'Australia',
                ],
            ])
            ->getMock();

        $this->entityManagerMock
            ->shouldReceive('createQuery')
            ->andReturn($queryMock);

        $customers = $this->customerRepository->findAllCustomers($page, $pageSize);

        $this->assertCount(1, $customers);
        $this->assertArrayHasKey('full_name', $customers[0]);
        $this->assertArrayHasKey('email', $customers[0]);
        $this->assertArrayHasKey('country', $customers[0]);
    }

    public function testFindExistingCustomerById()
    {
        // Mock for an existing customer with ID 1
        $queryMock = Mockery::mock(AbstractQuery::class);
        $queryMock->shouldReceive('setParameter')
            ->with('customerId', 1)
            ->andReturnSelf();
        $queryMock->shouldReceive('getOneOrNullResult')
            ->andReturn([
                'full_name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'username' => 'johndoe',
            ]);
    
        $this->entityManagerMock
            ->shouldReceive('createQuery')
            ->andReturn($queryMock);
    
        // Test for existing customer with ID 1
        $customer = $this->customerRepository->findCustomerById(1);
        $this->assertNotNull($customer);
        $this->assertArrayHasKey('full_name', $customer);
        $this->assertArrayHasKey('email', $customer);
        $this->assertArrayHasKey('username', $customer);
    }
    
    public function testFindNonExistingCustomerById()
    {
        // Mock for a non-existing customer with ID 999
        $queryMock = Mockery::mock(AbstractQuery::class);
        $queryMock->shouldReceive('setParameter')
            ->with('customerId', 999)
            ->andReturnSelf();
        $queryMock->shouldReceive('getOneOrNullResult')
            ->andReturn(null);
    
        $this->entityManagerMock
            ->shouldReceive('createQuery')
            ->andReturn($queryMock);
    
        // Test for non-existing customer with ID 999
        $nonExistingCustomer = $this->customerRepository->findCustomerById(999);
        $this->assertNull($nonExistingCustomer);
    }
}
