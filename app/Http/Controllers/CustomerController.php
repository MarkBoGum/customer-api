<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\CustomerRepository;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    protected $customerRepository;

    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * Retrieve a paginated list of customers.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $pageSize = (int) $request->query('pageSize', 10);
        $page = (int) $request->query('page', 1);

        // Set pageSize to 10 if less than 1 or exceeds 5000
        $pageSize = ($pageSize < 1 || $pageSize > 5000) ? 10 : $pageSize;
        $page = max($page, 1);

        $customers = $this->customerRepository->findAllCustomers($page, $pageSize);

        return response()->json([
            'status' => 'success',
            'data' => $customers,
        ]);
    }

    /**
     * Retrieve detailed information for a single customer.
     *
     * @param mixed $customerId
     * @return JsonResponse|mixed
     */
    public function show($customerId)
    {
        // Validate that the ID is an integer
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
    }
}
