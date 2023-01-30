<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\UserLog;
use App\Models\StockReturn;
use App\Http\Resources\ProductResource;
use App\Http\Resources\StockReturnResource;
use App\Http\Resources\StockReturnResourceWithRelationShip;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

use Barryvdh\DomPDF\Facade\PDF as Pdf;

class StockReturnController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $keywords = $request->keywords;
        $stock_return = StockReturn::where(function ($query) use ($keywords) {
            if ($keywords) {
                $query->where('transaction_number', 'like', '%' . $keywords . '%')
                    ->Orwhere('van_number', 'like', '%' . $keywords . '%');
            }
        })
            ->with('product', 'supplier', 'user')
            ->orderBy('id', 'DESC')
            ->paginate();

        return StockReturnResource::collection($stock_return);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show_all_stock_return_by_product(Request $request, $id)
    {
        $keywords = $request->keywords;
        $stock_return = StockReturn::where('product_id', $id)->where(function ($query) use ($keywords) {
            if ($keywords) {
                $query->where('transaction_number', 'like', '%' . $keywords . '%')
                    ->Orwhere('date', 'like', '%' . $keywords . '%')
                    ->Orwhere('van_number', 'like', '%' . $keywords . '%');
            }
        })
            ->with('product', 'supplier', 'user')
            ->orderBy('id', 'DESC')
            ->paginate();

        return StockReturnResource::collection($stock_return);
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
        $data = $request->only('supplier_id', 'product_id', 'user_id', 'transaction_number', 'van_number', 'date', 'quantity', 'remarks');
        $stock_return = StockReturn::create($data);
        $product = Product::find($stock_return->product_id);
        if($product != null) {
            $product->quantity = $product->quantity - $stock_return->quantity;
            $product->update();
        }

        UserLog::create([
            'user_id' => $user->id,
            'logs' => 'Product Management',
            'remarks' => 'Stock return to supplier with the product '. $product->product_name,
            'date' => Carbon::now()->format('Y-m-d'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        return new StockReturnResourceWithRelationShip($stock_return);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $stock_return = StockReturn::with('product', 'product.brands', 'product.categories', 'product.suppliers','user')->findOrFail($id);
        return new StockReturnResource($stock_return);
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
        $stock_return = StockReturn::find($id);
        $product = Product::find($stock_return->product_id);
        if($product != null) {
            $product->quantity = $product->quantity + $stock_return->quantity;
            $product->update();
        }
        $stock_return->supplier_id = $request->supplier_id;
        $stock_return->product_id = $request->product_id;
        $stock_return->user_id = $request->user_id;
        $stock_return->van_number = $request->van_number;
        $stock_return->date = $request->date;
        $stock_return->quantity = $request->quantity;
        $stock_return->remarks = $request->remarks;
        $new_product = Product::find($stock_return->product_id);
        if($new_product != null) {
            $new_product->quantity = $new_product->quantity - $stock_return->quantity;
            $new_product->update();
        }
        $stock_return->save();
        $stock_return->refresh();

        UserLog::create([
            'user_id' => $user->id,
            'logs' => 'Product Management',
            'remarks' => 'Update stock return detail on product '. $product->product_name,
            'date' => Carbon::now()->format('Y-m-d'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        return new StockReturnResourceWithRelationShip($stock_return);
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
        $stock_return = StockReturn::findOrFail($id);
        $stock_return->delete();

        UserLog::create([
            'user_id' => $user->id,
            'logs' => 'Product Management',
            'remarks' => 'Delete stock return on product'. $product->product_name,
            'date' => Carbon::now()->format('Y-m-d'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        return response()->noContent();
    }


        /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function generate_stock_return_report(Request $request) 
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $product_id = $request->product_id;
        $supplier_id = $request->supplier_id;

        if($supplier_id == 0 && $product_id == 0) {

            $stock = StockReturn::where('date', '>=', $start_date)->where('date', '<=', $end_date)->with('product:id,supplier_id,product_name,unit', 'user:id,name', 'product.suppliers:id,supplier_name')->select('product_id', 'user_id', 'van_number', 'date', 'remarks', DB::raw('sum(quantity) as quantity'))->groupBy('product_id','van_number', 'user_id', 'date', 'remarks')->orderBy('date', 'ASC')->get();

            $total = ($stock) ? ($stock)->sum('quantity') : 0;

        } else {

            if($supplier_id != 0 && $product_id == 0) {

                $stock = StockReturn::where('date', '>=', $start_date)
                ->where('date', '<=', $end_date)
                ->with('product:id,supplier_id,product_name,unit', 'user:id,name', 'product.suppliers:id,supplier_name')
                ->whereHas('product', function($query) use ($supplier_id) {
                    $query->where('supplier_id', $supplier_id);
                })
                ->select('product_id', 'user_id', 'van_number', 'date', 'remarks', DB::raw('sum(quantity) as quantity'))
                ->groupBy('product_id','van_number', 'user_id', 'date', 'remarks')
                ->orderBy('date', 'ASC')
                ->get();

                $total = ($stock) ? ($stock)->sum('quantity') : 0;
                
            } else if ($supplier_id == 0 && $product_id != 0) {

                $stock = StockReturn::where('date', '>=', $start_date)
                ->where('date', '<=', $end_date)
                ->where('product_id', $product_id)
                ->with('product:id,supplier_id,product_name,unit', 'user:id,name', 'product.suppliers:id,supplier_name')
                ->select('product_id', 'user_id', 'van_number', 'date', 'remarks', DB::raw('sum(quantity) as quantity'))
                ->groupBy('product_id','van_number', 'user_id', 'date', 'remarks')
                ->orderBy('date', 'ASC')
                ->get();

                $total = ($stock) ? ($stock)->sum('quantity') : 0;
            } else {

                $stock = StockReturn::where('date', '>=', $start_date)
                ->where('date', '<=', $end_date)
                ->where('product_id', $product_id)
                ->with('product:id,supplier_id,product_name,unit', 'user:id,name', 'product.suppliers:id,supplier_name')
                ->whereHas('product', function($query) use ($supplier_id) {
                    $query->where('supplier_id', $supplier_id);
                })
                ->select('*','product_id', 'user_id', 'van_number', 'date', 'remarks', DB::raw('sum(quantity) as quantity'))
                ->groupBy('product_id','van_number', 'user_id', 'date', 'remarks')
                ->orderBy('date', 'ASC')
                ->get();

                $total = ($stock) ? ($stock)->sum('quantity') : 0;;
            }
        }

    
        // return response()->json(['data' => $stock], Response::HTTP_OK);

        $pdf = PDF::loadView('pdf.stock-in-report', ['data' => $stock, 'total' => $total, 'report_type' => 'STOCK RETURN'])->setPaper('a4', 'landscape');
        return $pdf->stream();
    }

}
