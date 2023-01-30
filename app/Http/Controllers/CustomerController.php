<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Http\Resources\CustomerResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Auth;
use Exception;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $keywords = $request->keywords;
        $customer = Customer::where(function ($query) use ($keywords) {
            if ($keywords) {
                $query->where('customer_name', 'like', '%' . $keywords . '%')
                    ->Orwhere('gender', 'like', '%' . $keywords . '%')
                    ->Orwhere('email', 'like', '%' . $keywords . '%')
                    ->Orwhere('address', 'like', '%' . $keywords . '%')
                    ->Orwhere('phone_number', 'like', '%' . $keywords . '%');
            }
        })
            ->orderBy('id', 'DESC')
            ->paginate();

        return CustomerResource::collection($customer);
    }

        /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show_all_customer()
    {
        $customer = Customer::where('is_active', true)->get();
        return CustomerResource::collection($customer);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->only('customer_name', 'phone_number', 'gender', 'address', 'email', 'is_active', 'created_by');
        $customer = Customer::create($data);
        return new CustomerResource($customer);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $customer = Customer::findOrFail($id);

        return new CustomerResource($customer);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::find($id);

        $customer->customer_name = $request->customer_name;
        $customer->phone_number = $request->phone_number;
        $customer->address = $request->address;
        $customer->gender = $request->gender;
        $customer->email = $request->email;
        $customer->is_active = $request->is_active;
        $customer->save();

        return new CustomerResource($customer->refresh());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return response()->noContent();
    }
}
