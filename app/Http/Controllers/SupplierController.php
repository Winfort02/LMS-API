<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\UserLog;
use App\Http\Resources\SupplierResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Exception;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $keywords = $request->keywords;
        $supplier = Supplier::where(function ($query) use ($keywords) {
            if ($keywords) {
                $query->where('supplier_name', 'like', '%' . $keywords . '%')
                    ->Orwhere('address', 'like', '%' . $keywords . '%')
                    ->Orwhere('email', 'like', '%' . $keywords . '%');
            }
        })
            ->orderBy('id', 'DESC')
            ->paginate();

        return SupplierResource::collection($supplier);
    }

            /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show_all_supplier()
    {
        $supplier = Supplier::where('is_active', true)->get();
        return SupplierResource::collection($supplier);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $data = $request->only('supplier_name', 'contact_number', 'address', 'email', 'is_active', 'created_by');
        $supplier = Supplier::create($data);
        UserLog::create([
            'user_id' => $user->id,
            'logs' => 'Supplier Management',
            'remarks' => 'Added new supplier ' . $request->supplier_name,
            'date' => Carbon::now()->format('Y-m-d'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        return new SupplierResource($supplier);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $supplier = Supplier::findOrFail($id);

        return new SupplierResource($supplier);
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
        $user = Auth::user();
        $supplier = Supplier::find($id);
        $supplier->supplier_name = $request->supplier_name;
        $supplier->contact_number = $request->contact_number;
        $supplier->address = $request->address;
        $supplier->email = $request->email;
        $supplier->created_by = $request->created_by;
        $supplier->is_active = $request->is_active;
        $supplier->save();

        UserLog::create([
            'user_id' => $user->id,
            'logs' => 'Supplier Management',
            'remarks' => 'Update supplier detail ',
            'date' => Carbon::now()->format('Y-m-d'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        return new SupplierResource($supplier->refresh());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $supplier = Supplier::findOrFail($id);
        $supplier_name = $supplier->supplier_name;
        $supplier->delete();

        UserLog::create([
            'user_id' => $user->id,
            'logs' => 'Supplier Management',
            'remarks' => 'Delete supplier '. $supplier_name,
            'date' => Carbon::now()->format('Y-m-d'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        return response()->noContent();
    }
}
