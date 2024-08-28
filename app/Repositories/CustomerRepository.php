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

    public function save(Customer $customer)
    {
        $this->_em->persist($customer);
    }

    public function flush()
    {
        $this->_em->flush();
    }
}
