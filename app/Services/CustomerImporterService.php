<?php

namespace App\Services;

use App\Contracts\CustomerDataProviderInterface;
use App\Repositories\CustomerRepository;
use App\Entities\Customer;

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
        $pageSize = 50; // Default number of records to fetch per page
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

            // Flush remaining entities if any
            if ($batchCount > 0) {
                $this->customerRepository->flush();
            }

            // Decrement the remaining customers to fetch
            $remainingCustomers -= $currentPageSize;
        }
    }


    protected function mapCustomerData(Customer $customer, array $customerData)
    {
        $customer->setFirstName($customerData['name']['first']);
        $customer->setLastName($customerData['name']['last']);
        $customer->setEmail($customerData['email']);
        $customer->setUsername($customerData['login']['username']);
        $customer->setGender($customerData['gender']);
        $customer->setDob(new \DateTime($customerData['dob']['date']));
        $customer->setPhone($customerData['phone']);
        $customer->setCell($customerData['cell'] ?? null);
        $customer->setCountry($customerData['location']['country']);
        $customer->setCity($customerData['location']['city']);
        $customer->setState($customerData['location']['state'] ?? null);
        $customer->setStreetName($customerData['location']['street']['name'] ?? null);
        $customer->setStreetNumber($customerData['location']['street']['number'] ?? null);
        $customer->setPostcode($customerData['location']['postcode'] ?? null);
        $customer->setLatitude($customerData['location']['coordinates']['latitude'] ?? null);
        $customer->setLongitude($customerData['location']['coordinates']['longitude'] ?? null);
        $customer->setTimezoneOffset($customerData['location']['timezone']['offset'] ?? null);
        $customer->setTimezoneDescription($customerData['location']['timezone']['description'] ?? null);
        $customer->setPassword($customerData['login']['md5']);
        $customer->setPictureLarge($customerData['picture']['large'] ?? null);
        $customer->setPictureMedium($customerData['picture']['medium'] ?? null);
        $customer->setPictureThumbnail($customerData['picture']['thumbnail'] ?? null);
        $customer->setCreatedAt(new \DateTime());
        $customer->setUpdatedAt(new \DateTime());
    }
}
