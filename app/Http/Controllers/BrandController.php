<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\UserLog;
use App\Http\Resources\BrandResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Exception;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $keywords = $request->keywords;
        $brand = Brand::where(function ($query) use ($keywords) {
            if ($keywords) {
                $query->where('brand_name', 'like', '%' . $keywords . '%');
            }
        })
            ->orderBy('id', 'DESC')
            ->paginate();

        return BrandResource::collection($brand);
    }

        /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show_all_brand()
    {
        $brand = Brand::where('is_active', true)->get();
        return BrandResource::collection($brand);
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
        $data = $request->only('brand_name', 'description', 'is_active');
        $brand = Brand::create($data);
        UserLog::create([
            'user_id' => $user->id,
            'logs' => 'Brand Management',
            'remarks' => 'Added new brand '. $request->brand_name,
            'date' => Carbon::now()->format('Y-m-d'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        return new BrandResource($brand);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $brand = Brand::findOrFail($id);

        return new BrandResource($brand);
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
        $brand = Brand::find($id);
        $brand->brand_name = $request->brand_name;
        $brand->description = $request->description;
        $brand->is_active = $request->is_active;
        $brand->save();

        UserLog::create([
            'user_id' => $user->id,
            'logs' => 'Brand Management',
            'remarks' => 'Update brand detail ',
            'date' => Carbon::now()->format('Y-m-d'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        return new BrandResource($brand->refresh());
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
        $brand = Brand::findOrFail($id);
        $brand_name = $brand->brand_name;
        $brand->delete();

        UserLog::create([
            'user_id' => $user->id,
            'logs' => 'Brand Management',
            'remarks' => 'Delete brand name ' . $brand_name,
            'date' => Carbon::now()->format('Y-m-d'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        return response()->noContent();
    }
}
