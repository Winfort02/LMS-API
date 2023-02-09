<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\Customer;
use App\Models\OrderDetail;
use App\Models\UserLog;
use App\Http\Resources\ProductResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderDetailResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Auth;
use Exception;

use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\PDF as Pdf;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $keywords = $request->keywords;
        $customer_id = $request->customer_id;

        $start_date = $request->start_date;
        $end_date = $request->end_date;
        
        if($customer_id == 0) {

            $order = Order::where(function ($query) use ($keywords) {
                if ($keywords) {
                    $query->where('transaction_number', 'like', '%' . $keywords . '%')
                        ->Orwhere('sales_order_number', 'like', '%' . $keywords . '%')
                        ->orWhereHas('user', function ($query) use ($keywords){
                            if($keywords) {
                                $query->where('name', 'like', '%' . $keywords . '%');
                            }
                        });
                }
            })
                ->where('sales_date', '>=', $start_date)
                ->where('sales_date', '<=', $end_date)
                ->with('customer', 'user', 'order_details')
                ->orderBy('id', 'DESC')
                ->paginate();

        } else {

            $order = Order::where('customer_id', $customer_id)->where(function ($query) use ($keywords) {
                if ($keywords) {
                    $query->where('transaction_number', 'like', '%' . $keywords . '%')
                        ->Orwhere('sales_order_number', 'like', '%' . $keywords . '%');
                }
            })
                ->with('customer', 'user', 'order_details')
                ->orderBy('id', 'DESC')
                ->paginate();

        }

        return OrderResource::collection($order);
        
    }


    public function show_orders_by_user(Request $request, $user_id) {

        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $orders = Order::where('user_id', $user_id)
            ->where('sales_date', '>=', $start_date)
            ->where('sales_date', '<=', $end_date)
            ->with('customer', 'user', 'order_details')
            ->orderBy('id', 'DESC')
            ->paginate();

        return OrderResource::collection($orders);
    }


    public function show_orders_by_customer(Request $request, $customer_id) {

        $orders = Order::where('customer_id', $customer_id)
            ->with('customer', 'user', 'order_details')
            ->orderBy('id', 'DESC')
            ->paginate();

        return OrderResource::collection($orders);
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
        $data = $request->only('customer_id', 'user_id', 'transaction_number', 'sales_order_number', 'sales_date', 'payment_type', 'sales_type', 'total_amount', 'payment', 'remarks', 'status', 'items');

        $customer = Customer::findOrFail($request->customer_id);

        $order = Order::create([
            'customer_id' => $request->customer_id,
            'user_id' => $request->user_id,
            'transaction_number' => $request->transaction_number,
            'sales_order_number' => $request->sales_order_number,
            'sales_date' => $request->sales_date,
            'payment_type' => $request->payment_type,
            'payment' => $request->payment,
            'total_amount' => $request->total_amount,
            'order_status' => 'Completed',
            'sales_type' => $request->sales_type,
            'remarks' => $request->remarks,
            'status' => $request->status
        ]);

        if(count($data['items']) > 0) {

            foreach($data['items'] as $item) {

                $items[] = [
                    'id' => 0,
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'discount' => $item['discount'],
                    'sub_total' => $item['sub_total'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];

                $product = Product::find($item['product_id']);
                $product->quantity = $product->quantity - $item['quantity'];
                $product->update();
            }
            OrderDetail::insert($items);
        }

        UserLog::create([
            'user_id' => $user->id,
            'logs' => 'Generate Sales Order',
            'remarks' => 'Add new sales order to customer '. $customer->customer_name. ' with the total amount of '. $request->total_amount,
            'date' => Carbon::now()->format('Y-m-d'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        
        return new OrderResource($order);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $order = Order::with('customer', 'user', 'order_details')->findOrFail($id);
        return new OrderResource($order);
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
        $order = Order::with('order_details')->find($id);
        $order->customer_id = $request->customer_id;
        $order->sales_date = $request->sales_date;
        $order->payment_type = $request->payment_type;
        $order->remarks = $request->remarks;
        $order->status = $request->status;
        $order->sales_type = $request->sales_type;

        if($request->order_status === 'Cancel' && $order->order_status === 'Completed' && $request->order_status !== 'Completed') {

            if($order->order_details->count() > 0) {
                
                foreach($order->order_details as $items) {

                    $product = Product::find($items->product_id);
                    $product->quantity += $items->quantity;
                    $product->update();
                }
            }
            
            if($request->status == false) {
                $order->payment = 0;
            }
            $order->order_status = $request->order_status;
            $order->save();
            $order->refresh();

        } else if ($request->order_status === 'Completed' && $order->order_status === 'Cancel' && $request->order_status !== 'Cancel') {


            if($order->order_details->count() > 0) {

                foreach($order->order_details as $items) {

                    $product = Product::find($items->product_id);

                    if($product->quantity <= 0) {

                        return response()->json(['message' => 'Product '. $product->name . 'Out of stock']);
                    }

                    $product->quantity -= $items->quantity;
                    $product->update();
                }

                if($request->status == false) {
                    $order->payment = 0;
                } else {
                    $order->payment = $order->payment + $request->payment;
                }
                $order->order_status = $request->order_status;
                $order->save();
                $order->refresh();

            }
        } else if ($order->order_status === $request->order_status) {
            
            if($request->payment >= 0) {

                
                if($request->status == false) {
                    $order->payment = 0;
                } else {
                    $order->payment = $order->payment + $request->payment;
                }
                $order->order_status = $request->order_status;
                $order->save();
                $order->refresh();
            } else {

                if($request->status == false) {
                    $order->payment = 0;
                } else {
                    $order->payment = $order->payment + $request->payment;
                }
                $order->order_status = $request->order_status;
                $order->save();
                $order->refresh();
            }
            
        }

        UserLog::create([
            'user_id' => $user->id,
            'logs' => 'Sales',
            'remarks' => 'Update sales order detail with the SO# '. $order->sales_order_number,
            'date' => Carbon::now()->format('Y-m-d'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        
        return new OrderResource($order);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    //Export  Report PDF
    public function generate_sales_order_pdf($id)
    {
        $order = Order::with('customer', 'user', 'order_details', 'order_details.product')->findOrFail($id);
        $total = $order->order_details->sum('sub_total');
        $total_discount = $order->order_details->sum('discount');

        // return $order;
        $pdf = PDF::loadView('pdf.sales-order', ['data' => $order, 'total' => $total, 'total_discount' => $total_discount])->setPaper('a4', 'landscape');
        return $pdf->stream();
    }

    public function generate_sales_order_report(Request $request)
    {

        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $payment_type = $request->payment_type;
        $order_status = $request->order_status;
        $payment_status = $request->payment_status;
        $total_sales = 0;
            
        if($payment_type == 'CHECK/PDC') {

            if($order_status == 'CANCEL') {

                if($payment_status == 'PAID') {

                    $order = Order::where('payment_type', '=', 'CHECK/PDC')
                            ->where('order_status', '=', 'Cancel')
                            ->where('status', true)
                            ->where('sales_date', '>=', $start_date)
                            ->where('sales_date', '<=', $end_date)
                            ->with(['customer'])
                            ->get();
                } else if ($payment_status == 'PENDING') {

                    $order = Order::where('payment_type', '=', 'CHECK/PDC')
                            ->where('order_status', '=', 'Cancel')
                            ->where('status', false)
                            ->where('sales_date', '>=', $start_date)
                            ->where('sales_date', '<=', $end_date)
                            ->with(['customer'])
                            ->get();
                } else {

                    $order = Order::where('payment_type', '=', 'CHECK/PDC')
                            ->where('order_status', '=', 'Cancel')
                            ->where('sales_date', '>=', $start_date)
                            ->where('sales_date', '<=', $end_date)
                            ->with(['customer'])
                            ->get();
                }

            } else if ($order_status == 'COMPLETED') {

                if($payment_status == 'PAID') {

                    $order = Order::where('payment_type', '=', 'CHECK/PDC')
                            ->where('order_status', '=', 'Completed')
                            ->where('status', true)
                            ->where('sales_date', '>=', $start_date)
                            ->where('sales_date', '<=', $end_date)
                            ->with(['customer'])
                            ->get();
                } else if ($payment_status == 'PENDING') {

                    $order = Order::where('payment_type', '=', 'CHECK/PDC')
                            ->where('order_status', '=', 'Completed')
                            ->where('status', false)
                            ->where('sales_date', '>=', $start_date)
                            ->where('sales_date', '<=', $end_date)
                            ->with(['customer'])
                            ->get();
                } else {

                    $order = Order::where('payment_type', '=', 'CHECK/PDC')
                            ->where('order_status', '=', 'Completed')
                            ->where('sales_date', '>=', $start_date)
                            ->where('sales_date', '<=', $end_date)
                            ->with(['customer'])
                            ->get();
                }
            } else {

                if($payment_status == 'PAID') {

                    $order = Order::where('payment_type', '=', 'CHECK/PDC')
                            ->where('status', true)
                            ->where('sales_date', '>=', $start_date)
                            ->where('sales_date', '<=', $end_date)
                            ->with(['customer'])
                            ->get();
                } else if ($payment_status == 'PENDING') {

                    $order = Order::where('payment_type', '=', 'CHECK/PDC')
                            ->where('status', false)
                            ->where('sales_date', '>=', $start_date)
                            ->where('sales_date', '<=', $end_date)
                            ->with(['customer'])
                            ->get();
                } else {

                    $order = Order::where('payment_type', '=', 'CHECK/PDC')
                            ->where('sales_date', '>=', $start_date)
                            ->where('sales_date', '<=', $end_date)
                            ->with(['customer'])
                            ->get();
                }
            }

        } else if ($payment_type == 'CASH') {

            if($order_status == 'CANCEL') {

                if($payment_status == 'PAID') {

                    $order = Order::where('payment_type', '=', 'CASH')
                            ->where('order_status', '=', 'Cancel')
                            ->where('status', true)
                            ->where('sales_date', '>=', $start_date)
                            ->where('sales_date', '<=', $end_date)
                            ->with(['customer'])
                            ->get();
                } else if ($payment_status == 'PENDING') {

                    $order = Order::where('payment_type', '=', 'CASH')
                            ->where('order_status', '=', 'Cancel')
                            ->where('status', false)
                            ->where('sales_date', '>=', $start_date)
                            ->where('sales_date', '<=', $end_date)
                            ->with(['customer'])
                            ->get();
                } else {

                    $order = Order::where('payment_type', '=', 'CASH')
                            ->where('order_status', '=', 'Cancel')
                            ->where('sales_date', '>=', $start_date)
                            ->where('sales_date', '<=', $end_date)
                            ->with(['customer'])
                            ->get();
                }

            } else if ($order_status == 'COMPLETED') {

                if($payment_status == 'PAID') {

                    $order = Order::where('payment_type', '=', 'CASH')
                            ->where('order_status', '=', 'Completed')
                            ->where('status', true)
                            ->where('sales_date', '>=', $start_date)
                            ->where('sales_date', '<=', $end_date)
                            ->with(['customer'])
                            ->get();
                } else if ($payment_status == 'PENDING') {

                    $order = Order::where('payment_type', '=', 'CASH')
                            ->where('order_status', '=', 'Completed')
                            ->where('status', false)
                            ->where('sales_date', '>=', $start_date)
                            ->where('sales_date', '<=', $end_date)
                            ->with(['customer'])
                            ->get();
                } else {

                    $order = Order::where('payment_type', '=', 'CASH')
                            ->where('order_status', '=', 'Completed')
                            ->where('sales_date', '>=', $start_date)
                            ->where('sales_date', '<=', $end_date)
                            ->with(['customer'])
                            ->get();
                }
            } else {

                if($payment_status == 'PAID') {

                    $order = Order::where('payment_type', '=', 'CASH')
                            ->where('status', true)
                            ->where('sales_date', '>=', $start_date)
                            ->where('sales_date', '<=', $end_date)
                            ->with(['customer'])
                            ->get();
                } else if ($payment_status == 'PENDING') {

                    $order = Order::where('payment_type', '=', 'CASH')
                            ->where('status', false)
                            ->where('sales_date', '>=', $start_date)
                            ->where('sales_date', '<=', $end_date)
                            ->with(['customer'])
                            ->get();
                } else {

                    $order = Order::where('payment_type', '=', 'GCASH')
                            ->where('sales_date', '>=', $start_date)
                            ->where('sales_date', '<=', $end_date)
                            ->with(['customer'])
                            ->get();
                }
            }
        } else {

            if($order_status == 'CANCEL') {

                if($payment_status == 'PAID') {

                    $order = Order::where('order_status', '=', 'Cancel')
                            ->where('status', true)
                            ->where('sales_date', '>=', $start_date)
                            ->where('sales_date', '<=', $end_date)
                            ->with(['customer'])
                            ->get();
                } else if ($payment_status == 'PENDING') {

                    $order = Order::where('order_status', '=', 'Cancel')
                            ->where('status', false)
                            ->where('sales_date', '>=', $start_date)
                            ->where('sales_date', '<=', $end_date)
                            ->with(['customer'])
                            ->get();
                } else {

                    $order = Order::where('order_status', '=', 'Cancel')
                            ->where('sales_date', '>=', $start_date)
                            ->where('sales_date', '<=', $end_date)
                            ->with(['customer'])
                            ->get();
                }

            } else if ($order_status == 'COMPLETED') {

                if($payment_status == 'PAID') {

                    $order = Order::where('order_status', '=', 'Completed')
                            ->where('status', true)
                            ->where('sales_date', '>=', $start_date)
                            ->where('sales_date', '<=', $end_date)
                            ->with(['customer'])
                            ->get();
                } else if ($payment_status == 'PENDING') {

                    $order = Order::where('order_status', '=', 'Completed')
                            ->where('status', false)
                            ->where('sales_date', '>=', $start_date)
                            ->where('sales_date', '<=', $end_date)
                            ->with(['customer'])
                            ->get();
                } else {

                    $order = Order::where('order_status', '=', 'Completed')
                            ->where('sales_date', '>=', $start_date)
                            ->where('sales_date', '<=', $end_date)
                            ->with(['customer'])
                            ->get();
                }
            } else {

                if($payment_status == 'PAID') {

                    $order = Order::where('status', true)
                            ->where('sales_date', '>=', $start_date)
                            ->where('sales_date', '<=', $end_date)
                            ->with(['customer'])
                            ->get();
                } else if ($payment_status == 'PENDING') {

                    $order = Order::where('status', false)
                            ->where('sales_date', '>=', $start_date)
                            ->where('sales_date', '<=', $end_date)
                            ->with(['customer'])
                            ->get();
                } else {

                    $order = Order::where('sales_date', '>=', $start_date)
                            ->where('sales_date', '<=', $end_date)
                            ->with(['customer'])
                            ->get();
                }
            }
        }
        if($order->count() > 0) {
            $total_sales = ($order) ? $order->sum('total_amount') : 0;
            $total_cash = ($order) ? $order->sum('total_amount') : 0;
        } else {
            $total_sales = 0;
            $total_cash = 0;
        }
        $pdf = PDF::loadView('pdf.sales-order-report', ['data' => $order, 'start_date' => $start_date, 'end_date' => $end_date, 'total_sales' => $total_sales, 'total_cash' => $total_cash])->setPaper('a4', 'landscape');
        return $pdf->stream();

    }

    public function generate_order_items_summary_report(Request $request) 
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $order_items = OrderDetail::with('product:id,product_name,unit,selling_price','order', 'order.customer', 'order.user:id,name')->whereHas('order', function ($query) use ($start_date, $end_date) {
            $query->where('sales_date', '>=', $start_date)->where('sales_date', '<=', $end_date);
        })->get();

        $total = ($order_items->count() > 0) ? $order_items->sum('sub_total') : 0;
        $total_qty = ($order_items->count() > 0) ? $order_items->sum('quantity') : 0;
        // $total_price = ($order_items->count() > 0) ? $order_items->sum('price') : 0;
        $total_discount = ($order_items->count() > 0) ? $order_items->sum('discount') : 0;

        // return response()->json(['data' => $order_items, 'start_date' => $start_date, 'end_date' => $end_date, 'total' => $total], Response::HTTP_OK);

        $pdf = PDF::loadView('pdf.sales-order-summary-report', ['data' => $order_items, 'start_date' => $start_date, 'end_date' => $end_date, 'total' => $total, 'total_qty' => $total_qty, 'total_discount' => $total_discount])->setPaper('a4', 'landscape');
        return $pdf->stream();
    }
}
