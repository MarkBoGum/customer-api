<?php

namespace App\Services;

use App\Contracts\CustomerDataProviderInterface;
use App\Repositories\CustomerRepository;
use App\Entities\Customer;
use Illuminate\Support\Facades\Log;

class CustomerImporterService
{
    protected $dataProvider;
    protected $customerRepository;
    protected $batchSize = 20; // Number of records to persist before flushing

    public function __construct(CustomerDataProviderInterface $dataProvider, CustomerRepository $customerRepository)
    {
        $this->dataProvider = $dataProvider;
        $this->customerRepository = $customerRepository;
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
                    $customer = $this->customerRepository->findByEmail($customerData['email']);

                    if (!$customer) {
                        $customer = new Customer();
                    }

                    $this->mapCustomerData($customer, $customerData);
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
            Log::error("Failed to import customers: " . $e->getMessage());
            throw $e;
        }
    }

    protected function mapCustomerData(Customer $customer, array $customerData)
    {
        $customer->setFirstName($customerData['name']['first']);
        $customer->setLastName($customerData['name']['last']);
        $customer->setEmail($customerData['email']);
        $customer->setUsername($customerData['login']['username']);
        $customer->setGender($customerData['gender']);
        $customer->setCountry($customerData['location']['country']);
        $customer->setCity($customerData['location']['city']);
        $customer->setPhone($customerData['phone']);
        $customer->setPassword(md5($customerData['login']['password']));
        $customer->setCreatedAt(new \DateTime());
        $customer->setUpdatedAt(new \DateTime());
    }
}
