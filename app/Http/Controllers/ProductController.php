<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\UserLog;
use App\Models\OrderDetail;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\PDF as Pdf;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $keywords = $request->keywords;
        $category_id = $request->category_id;

        if($category_id == 0) {
            $product = Product::where(function ($query) use ($keywords) {
                if ($keywords) {
                    $query->where('product_name', 'like', '%' . $keywords . '%')
                        ->orWhereHas('categories', function ($query) use ($keywords){
                            if($keywords) {
                                $query->where('category_name', 'like', '%' . $keywords . '%');
                            }
                        })
                        ->orWhereHas('brands', function ($query) use ($keywords){
                            if($keywords) {
                                $query->where('brand_name', 'like', '%' . $keywords . '%');
                            }
                        })
                        ->orWhereHas('suppliers', function ($query) use ($keywords){
                            if($keywords) {
                                $query->where('supplier_name', 'like', '%' . $keywords . '%');
                            }
                        });
                    }
                })
                ->with('categories', 'brands', 'suppliers')
                ->orderBy('id', 'DESC')
                ->paginate();
        } else {

            $product = Product::where('category_id', $category_id)->where(function ($query) use ($keywords) {
                if ($keywords) {
                    $query->where('product_name', 'like', '%' . $keywords . '%');
                }
            })
                ->with('categories', 'brands', 'suppliers')
                ->orderBy('id', 'DESC')
                ->paginate();
        }
        return ProductResource::collection($product);
       
    }

        /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show_all_product()
    {
        $product = Product::with('brands:id,brand_name')->where('is_active', true)->get();
        return ProductResource::collection($product);
    }


            /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show_all_product_by_supplier(Request $request, $id)
    {
        $keywords = $request->keywords;
        $product = Product::where('supplier_id', $id)->where(function ($query) use ($keywords) {
            if ($keywords) {
                $query->where('product_name', 'like', '%' . $keywords . '%');
            }
        })
            ->where('is_active', true)
            ->with('categories', 'brands', 'suppliers')
            ->orderBy('id', 'DESC')
            ->paginate();

        return ProductResource::collection($product);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $file = $request->hasFile('image');

        $user = Auth::user();

        if(!$file || $request->image == null) {
            
            $imageName = 'default-image.png';

        } else {
            $imageName = $request->file('image')->hashName();
            $file_path = Storage::disk('local')->put('public/images/'. $imageName, file_get_contents($request->file('image')));
        }
            

        
        $product = Product::create([
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'supplier_id' => $request->supplier_id,
            'image' => $imageName,
            'product_name' => $request->product_name,
            'description' => $request->description,
            'base_price' => $request->base_price,
            'selling_price' => $request->selling_price,
            'quantity' => $request->quantity,
            'unit' => $request->unit,
            'is_active' => $request->is_active == 'true' ? 1 : 0,
            'created_by' => $request->created_by
        ]);

        UserLog::create([
            'user_id' => $user->id,
            'logs' => 'Product Management',
            'remarks' => 'Added new product '. $request->product_name,
            'date' => Carbon::now()->format('Y-m-d'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        return new ProductResource($product);
    }

        /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update_product(Request $request, $id)
    {

        $user = Auth::user();
        $product = Product::find($id);
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;
        $product->supplier_id = $request->supplier_id;
        $product->product_name = $request->product_name;
        $product->description = $request->description;
        $product->base_price = $request->base_price;
        $product->selling_price = $request->selling_price;
        // $product->quantity = $request->quantity;
        $product->unit = $request->unit;
        $product->is_active = $request->is_active == 'true' ? 1 : 0;

        if($request->hasFile('image')) {
            // remove current image 
            Storage::delete('public/images/'. $product->image);

            // replace new image
            $imageName = $request->file('image')->hashName();
            Storage::disk('local')->put('public/images/'. $imageName, file_get_contents($request->file('image')));
            $product->image = $imageName;
            $product->update();
        }
        $product->update();

        UserLog::create([
            'user_id' => $user->id,
            'logs' => 'Product Management',
            'remarks' => 'Update product detail',
            'date' => Carbon::now()->format('Y-m-d'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        return new ProductResource($product->refresh());
    }   

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::findOrFail($id);

        return new ProductResource($product);
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
        $product = Product::findOrFail($id);

        if($product->image !== 'default-image.png') {
            
            Storage::delete('public/images/'. $product->image);
        }
        
        $product_name = $product->product_name;
        $product->delete();

        UserLog::create([
            'user_id' => $user->id,
            'logs' => 'Product Management',
            'remarks' => 'Deleted product '. $product_name,
            'date' => Carbon::now()->format('Y-m-d'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        return response()->noContent();
    }


    public function generate_product_inventory(Request $request) {
        
        $status = $request->status;
        $supplier = $request->supplier_id;

        if($request->status === 'OUT OF STOCK') {

            if($supplier === 0) {

                // $product = Product::where('quantity', '<=', 0)->with(['suppliers', 'order_details'])->orderBy('id', 'ASC')->get();

                $product = Product::where('quantity', '<=', 0)
                    ->with([
                        'brands', 
                        'order_details' => function ($query) {
                            $query->join('orders', 'orders.id', '=', 'order_id')->selectRaw('SUM(CASE WHEN orders.order_status = "Completed" THEN quantity ELSE 0 END) AS sold, SUM(CASE WHEN orders.order_status = "Cancel" THEN quantity ELSE 0 END) AS cancel, product_id')->groupBy('product_id');
                        },
                        'product_stock_in' => function ($query) {
                            $query->selectRaw('sum(quantity) as quantity, product_id')->groupBy('product_id');
                        },
                        'product_stock_return' => function ($query) {
                            $query->selectRaw('sum(quantity) as quantity, product_id')->groupBy('product_id');
                        }])
                        ->orderBy('id', 'ASC')
                        ->get();

                $pdf = PDF::loadView('pdf.product-inventory', ['data' => $product])->setPaper('a4', 'landscape');
                return $pdf->stream();

            } else {

                // $product = Product::where('quantity', '<=', 0)->where('supplier_id', $supplier)->with(['suppliers', 'order_details'])->orderBy('id', 'ASC')->get();

                $product = Product::where('supplier_id', $supplier)
                    ->where('quantity', '<=', 0)
                    ->with([
                        'brands', 
                        'order_details' => function ($query) {
                            $query->join('orders', 'orders.id', '=', 'order_id')->selectRaw('SUM(CASE WHEN orders.order_status = "Completed" THEN quantity ELSE 0 END) AS sold, SUM(CASE WHEN orders.order_status = "Cancel" THEN quantity ELSE 0 END) AS cancel, product_id')->groupBy('product_id');
                        },
                        'product_stock_in' => function ($query) {
                            $query->selectRaw('sum(quantity) as quantity, product_id')->groupBy('product_id');
                        },
                        'product_stock_return' => function ($query) {
                            $query->selectRaw('sum(quantity) as quantity, product_id')->groupBy('product_id');
                        }])
                        ->orderBy('id', 'ASC')
                        ->get();

                $pdf = PDF::loadView('pdf.product-inventory', ['data' => $product])->setPaper('a4', 'landscape');
                return $pdf->stream();
            }

        } else if ($request->status == 'AVAILABLE') {

            if($supplier === 0) {

                $product = Product::where('quantity', '>', 0)
                    ->with([
                        'brands', 
                        'order_details' => function ($query) {
                            $query->join('orders', 'orders.id', '=', 'order_id')->selectRaw('SUM(CASE WHEN orders.order_status = "Completed" THEN quantity ELSE 0 END) AS sold, SUM(CASE WHEN orders.order_status = "Cancel" THEN quantity ELSE 0 END) AS cancel, product_id')->groupBy('product_id');
                        },
                        'product_stock_in' => function ($query) {
                            $query->selectRaw('sum(quantity) as quantity, product_id')->groupBy('product_id');
                        },
                        'product_stock_return' => function ($query) {
                            $query->selectRaw('sum(quantity) as quantity, product_id')->groupBy('product_id');
                        }])
                        ->orderBy('id', 'ASC')
                        ->get();

                $pdf = PDF::loadView('pdf.product-inventory', ['data' => $product])->setPaper('a4', 'landscape');
                return $pdf->stream();

            } else {

                $product = Product::where('supplier_id', $supplier)
                    ->where('quantity', '>', 0)
                    ->with([
                        'brands', 
                        'order_details' => function ($query) {
                            $query->join('orders', 'orders.id', '=', 'order_id')->selectRaw('SUM(CASE WHEN orders.order_status = "Completed" THEN quantity ELSE 0 END) AS sold, SUM(CASE WHEN orders.order_status = "Cancel" THEN quantity ELSE 0 END) AS cancel, product_id')->groupBy('product_id');
                        },
                        'product_stock_in' => function ($query) {
                            $query->selectRaw('sum(quantity) as quantity, product_id')->groupBy('product_id');
                        },
                        'product_stock_return' => function ($query) {
                            $query->selectRaw('sum(quantity) as quantity, product_id')->groupBy('product_id');
                        }])
                        ->orderBy('id', 'ASC')
                        ->get();

                $pdf = PDF::loadView('pdf.product-inventory', ['data' => $product])->setPaper('a4', 'landscape');
                return $pdf->stream();
            }
        } else if ($request->status == 'ALL' ) {

            if($supplier === 0) {

                $product = Product::orderBy('id', 'ASC')
                    ->with([
                        'brands', 
                        'order_details' => function ($query) {
                            $query->join('orders', 'orders.id', '=', 'order_id')->selectRaw('SUM(CASE WHEN orders.order_status = "Completed" THEN quantity ELSE 0 END) AS sold, SUM(CASE WHEN orders.order_status = "Cancel" THEN quantity ELSE 0 END) AS cancel, product_id')->groupBy('product_id');
                        },
                        'product_stock_in' => function ($query) {
                            $query->selectRaw('sum(quantity) as quantity, product_id')->groupBy('product_id');
                        },
                        'product_stock_return' => function ($query) {
                            $query->selectRaw('sum(quantity) as quantity, product_id')->groupBy('product_id');
                        }])
                        ->get();
                
                $pdf = PDF::loadView('pdf.product-inventory', ['data' => $product])->setPaper('a4', 'landscape');
                return $pdf->stream();

            } else {

                $product = Product::where('supplier_id', $supplier)
                    ->with([
                        'brands', 
                        'order_details' => function ($query) {
                            $query->join('orders', 'orders.id', '=', 'order_id')->selectRaw('SUM(CASE WHEN orders.order_status = "Completed" THEN quantity ELSE 0 END) AS sold, SUM(CASE WHEN orders.order_status = "Cancel" THEN quantity ELSE 0 END) AS cancel, product_id')->groupBy('product_id');
                        },
                        'product_stock_in' => function ($query) {
                            $query->selectRaw('sum(quantity) as quantity, product_id')->groupBy('product_id');
                        },
                        'product_stock_return' => function ($query) {
                            $query->selectRaw('sum(quantity) as quantity, product_id')->groupBy('product_id');
                        }])
                        ->orderBy('id', 'ASC')
                        ->get();

                $pdf = PDF::loadView('pdf.product-inventory', ['data' => $product])->setPaper('a4', 'landscape');
                return $pdf->stream();
            }
        }
    }
}
