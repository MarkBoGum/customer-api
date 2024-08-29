<?php

namespace Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\DataFixtures\CustomerFixtures;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('doctrine:schema:drop', ['--force' => true]);
        $this->entityManager = $this->app->make(EntityManagerInterface::class);
        $this->artisan('doctrine:schema:create');
        $this->loadFixtures();
    }

    protected function loadFixtures()
    {
        $loader = new Loader();
        $loader->addFixture(new CustomerFixtures());

        $purger = new ORMPurger();
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute($loader->getFixtures(), true);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
