<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entities\Customer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CustomerRepository::class, function ($app) {
            return $app->make(EntityManagerInterface::class)->getRepository(Customer::class);
        });

        $this->app->bind(
            \App\Contracts\CustomerDataProviderInterface::class, 
            \App\DataProviders\RandomUserDataProvider::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
