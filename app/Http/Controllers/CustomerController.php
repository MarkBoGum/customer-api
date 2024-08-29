<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\CustomerRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    protected $customerRepository;
    protected $defaultPageSize;
    protected $maxPageSize;

    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
        $this->defaultPageSize = config('pagination.default_size', 10);
        $this->maxPageSize = config('pagination.max_size', 100);
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $page = (int) $request->query('page', 1);
            $pageSize = (int) $request->query('pageSize', $this->defaultPageSize);

            $page = max($page, 1);
            if ($pageSize < 1 || $pageSize > $this->maxPageSize) {
                $pageSize = $this->defaultPageSize;
            }

            $customers = $this->customerRepository->findAllCustomers($page, $pageSize);

            return response()->json([
                'status' => 'success',
                'data' => $customers,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to retrieve customers: " . $e->getMessage());
            return response()->json(['message' => 'Unable to retrieve customers'], 500);
        }
    }

    public function show($customerId)
    {
        try {
            if (!ctype_digit($customerId)) {
                return response()->json(['message' => 'Invalid customer ID'], 400);
            }

            $customer = $this->customerRepository->findCustomerById((int) $customerId);
        
            if (!$customer) {
                return response()->json(['message' => 'Customer not found'], 404);
            }
        
            return response()->json([
                'status' => 'success',
                'data' => $customer,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Failed to retrieve customer: " . $e->getMessage());
            return response()->json(['message' => 'Unable to retrieve customer'], 500);
        }
    }
}
