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
        try {
            
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

        } catch (Exception $e) {
            
            if($e->getCode() == 0) {
                return response()->json(['message' => 'BRAND NOT FOUND'], Response::HTTP_NOT_FOUND);
            } else
                return response()->json(['message' => 'SERVER ERROR'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

        /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show_all_customer()
    {
        try {
            
            $customer = Customer::where('is_active', true)->get();
            return CustomerResource::collection($customer);

        } catch (Exception $e) {
            
            if($e->getCode() == 0) {
                return response()->json(['message' => 'BRAND NOT FOUND'], Response::HTTP_NOT_FOUND);
            } else
                return response()->json(['message' => 'SERVER ERROR'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            
            $data = $request->only('customer_name', 'phone_number', 'gender', 'address', 'email', 'is_active', 'created_by');
            $customer = Customer::create($data);
            return new CustomerResource($customer);

        } catch (Exception $e) {
            
            if($e->getCode() == 0) {
                return response()->json(['message' => 'BRAND NOT FOUND'], Response::HTTP_NOT_FOUND);
            } else
                return response()->json(['message' => 'SERVER ERROR'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            
            $customer = Customer::findOrFail($id);

            return new CustomerResource($customer);

        } catch (Exception $e) {
            
            if($e->getCode() == 0) {
                return response()->json(['message' => 'BRAND NOT FOUND'], Response::HTTP_NOT_FOUND);
            } else
                return response()->json(['message' => 'SERVER ERROR'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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
        try {
            
            $customer = Customer::find($id);

            $customer->customer_name = $request->customer_name;
            $customer->phone_number = $request->phone_number;
            $customer->address = $request->address;
            $customer->gender = $request->gender;
            $customer->email = $request->email;
            $customer->is_active = $request->is_active;
            $customer->save();

            return new CustomerResource($customer->refresh());

        } catch (Exception $e) {
            
            if($e->getCode() == 0) {
                return response()->json(['message' => 'BRAND NOT FOUND'], Response::HTTP_NOT_FOUND);
            } else
                return response()->json(['message' => 'SERVER ERROR'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            
            $customer = Customer::findOrFail($id);
            $customer->delete();

            return response()->noContent();

        } catch (Exception $e) {
            
            if($e->getCode() == 0) {
                return response()->json(['message' => 'BRAND NOT FOUND'], Response::HTTP_NOT_FOUND);
            } else
                return response()->json(['message' => 'SERVER ERROR'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
