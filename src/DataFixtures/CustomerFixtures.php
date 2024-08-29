<?php

namespace App\DataFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entities\Customer;
use Carbon\Carbon;

class CustomerFixtures implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i <= 10; $i++) {
            $customer = new Customer();
            $customer->setFirstName("FirstName $i");
            $customer->setLastName("LastName $i");
            $customer->setEmail("customer$i@example.com");
            $customer->setUsername("username$i");
            $customer->setGender($i % 2 == 0 ? 'male' : 'female');
            $customer->setCountry('Australia');
            $customer->setCity("City $i");
            $customer->setPhone("123-456-789$i");
            $customer->setPassword(md5("password$i"));
            $customer->setCreatedAt(Carbon::now());
            $customer->setUpdatedAt(Carbon::now());

            $manager->persist($customer);
        }

        $manager->flush();
    }
}
