<?php

namespace App\Services;

use App\Contracts\CustomerDataProviderInterface;
use App\Repositories\CustomerRepository;
use App\Entities\Customer;
use App\Transformers\RandomUserTransformer;
use Psr\Log\LoggerInterface;

class CustomerImporterService
{
    protected $dataProvider;
    protected $customerRepository;
    protected $randomUserTransformer;
    protected $logger;
    protected $batchSize = 20; // Number of records to persist before flushing

    public function __construct(
        CustomerDataProviderInterface $dataProvider,
        CustomerRepository $customerRepository,
        RandomUserTransformer $randomUserTransformer,
        LoggerInterface $logger
    ) {
        $this->dataProvider = $dataProvider;
        $this->customerRepository = $customerRepository;
        $this->randomUserTransformer = $randomUserTransformer;
        $this->logger = $logger;
    }

    public function importCustomers(int $count): void
    {
        try {
            $pageSize = 50;
            $totalPages = ceil($count / $pageSize);
            $remainingCustomers = $count;

            for ($page = 1; $page <= $totalPages; $page++) {
                // Adjust pageSize for the last page or when count is less than pageSize
                $currentPageSize = min($remainingCustomers, $pageSize);
                $customers = $this->dataProvider->fetchCustomers($page, $currentPageSize);
                $batchCount = 0;

                foreach ($customers as $customerData) {
                    $transformedData = $this->randomUserTransformer->transform($customerData);
                    $customer = $this->customerRepository->findByEmail($transformedData['email']);

                    if (!$customer) {
                        $customer = new Customer();
                    }

                    $this->mapCustomerData($customer, $transformedData);
                    $this->customerRepository->save($customer);

                    if (++$batchCount === $this->batchSize) {
                        $this->customerRepository->flush();
                        $batchCount = 0;
                    }
                }

                if ($batchCount > 0) {
                    $this->customerRepository->flush();
                }

                $remainingCustomers -= $currentPageSize;
            }
        } catch (\Exception $e) {
            $this->logger->error("Failed to import customers: " . $e->getMessage());
            throw $e;
        }
    }

    protected function mapCustomerData(Customer $customer, array $customerData)
    {
        $customer->setFirstName($customerData['first_name']);
        $customer->setLastName($customerData['last_name']);
        $customer->setEmail($customerData['email']);
        $customer->setUsername($customerData['username']);
        $customer->setGender($customerData['gender']);
        $customer->setCountry($customerData['country']);
        $customer->setCity($customerData['city']);
        $customer->setPhone($customerData['phone']);
        $customer->setPassword($customerData['password']);
        $customer->setCreatedAt($customerData['created_at']);
        $customer->setUpdatedAt($customerData['updated_at']);
    }
}
