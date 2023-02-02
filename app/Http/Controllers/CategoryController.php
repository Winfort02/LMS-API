<?php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\UserLog;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Exception;

class CategoryController extends Controller
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
            $category = Category::where(function ($query) use ($keywords) {
                if ($keywords) {
                    $query->where('category_name', 'like', '%' . $keywords . '%');
                }
            })
                ->orderBy('id', 'DESC')
                ->paginate();

            return CategoryResource::collection($category);

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
    public function show_all_category()
    {
        try {

            $category = Category::where('is_active', true)->get();
            return CategoryResource::collection($category);

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
            
            $category = Category::findOrFail($id);
            return new CategoryResource($category);

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
            
            //create Category
            $user = Auth::user();
            $data = $request->only('category_name', 'description', 'is_active');
            $category = Category::create($data);
            UserLog::create([
                'user_id' => $user->id,
                'logs' => 'Category Management',
                'remarks' => 'Added new category ' . $request->category_name,
                'date' => Carbon::now()->format('Y-m-d'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            return new CategoryResource($category);

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
            
            $user = Auth::user();
            $category = Category::find($id);
            $category->category_name = $request->category_name;
            $category->description = $request->description;
            $category->is_active = $request->is_active;
            $category->save();

            UserLog::create([
                'user_id' => $user->id,
                'logs' => 'Category Management',
                'remarks' => 'Update category detail',
                'date' => Carbon::now()->format('Y-m-d'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            return new CategoryResource($category->refresh());

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
            
            $user = Auth::user();
            $category = Category::findOrFail($id);
            $cat_name = $category->category_name;
            $category->delete();

            UserLog::create([
                'user_id' => $user->id,
                'logs' => 'Category Management',
                'remarks' => 'Delete category '. $cat_name,
                'date' => Carbon::now()->format('Y-m-d'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            return response()->noContent();
            
        } catch (Exception $e) {
            
            if($e->getCode() == 0) {
                return response()->json(['message' => 'BRAND NOT FOUND'], Response::HTTP_NOT_FOUND);
            } else
                return response()->json(['message' => 'SERVER ERROR'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }




}
