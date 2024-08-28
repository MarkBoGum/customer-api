<?php

namespace App\Repositories;

use Doctrine\ORM\EntityRepository;
use App\Entities\Customer;

class CustomerRepository extends EntityRepository
{
    public function findByEmail(string $email): ?Customer
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function save(Customer $customer): void
    {
        $this->_em->persist($customer);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }

    /**
     * Find all customers with pagination.
     *
     * @param int $page The page number (1-based index).
     * @param int $pageSize The number of records per page.
     * @return array An array of customer data.
     */
    public function findAllCustomers(int $page, int $pageSize): array
    {
        $offset = ($page - 1) * $pageSize;

        $query = $this->_em->createQuery(
            'SELECT CONCAT(c.firstName, \' \', c.lastName) AS full_name, c.email, c.country
            FROM App\Entities\Customer c
            ORDER BY c.id ASC'
        )
        ->setFirstResult($offset)
        ->setMaxResults($pageSize);

        return $query->getArrayResult();
    }

    /**
     * Find a single customer by ID with detailed information.
     *
     * @param int $customerId The ID of the customer.
     * @return array|null An array of customer details or null if not found.
     */
    public function findCustomerById(int $customerId): ?array
    {
        $query = $this->_em->createQuery(
            'SELECT CONCAT(c.firstName, \' \', c.lastName) AS full_name, c.email, c.username, c.gender, c.country, c.city, c.phone
            FROM App\Entities\Customer c
            WHERE c.id = :customerId'
        )
        ->setParameter('customerId', $customerId);

        return $query->getOneOrNullResult();
    }
}
