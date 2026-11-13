<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

use App\Http\Requests\CustomerStoreRequest;
use App\Http\Resources\CustomerResource;

class CustomerController extends Controller
{

    public function index()
    {
        return CustomerResource::collection(Customer::orderBy('name')->get());
    }

        public function store(CustomerStoreRequest $request)
    {
        return (new CustomerResource(Customer::create($request->validated())))
            ->response()->setStatusCode(201);
    }

    public function show(Customer $customer)
    {
        return new CustomerResource($customer);
    }
}