<?php

namespace App\Repositories;

use Doctrine\ORM\EntityRepository;
use App\Entities\Customer;
use Doctrine\ORM\Exception\ORMException;
use Illuminate\Support\Facades\Log;

class CustomerRepository extends EntityRepository
{
    public function findByEmail(string $email): ?Customer
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function save(Customer $customer): void
    {
        try {
            $this->_em->persist($customer);
        } catch (ORMException $e) {
            Log::error("Failed to save customer: " . $e->getMessage());
            throw $e;
        }
    }

    public function flush(): void
    {
        try {
            $this->_em->flush();
        } catch (ORMException $e) {
            Log::error("Failed to flush customer changes: " . $e->getMessage());
            throw $e;
        }
    }

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
