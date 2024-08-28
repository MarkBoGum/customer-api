<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CustomerImporterService;

class ImportCustomersCommand extends Command
{
    protected $signature = 'customers:import {count=100}';
    protected $description = 'Import customers from the Random User API';

    protected $importerService;

    public function __construct(CustomerImporterService $importerService)
    {
        parent::__construct();
        $this->importerService = $importerService;
    }

    public function handle()
    {
        $count = (int) $this->argument('count');
        $this->importerService->importCustomers($count);
        $this->info("Customer import process completed successfully.");
    }
}
