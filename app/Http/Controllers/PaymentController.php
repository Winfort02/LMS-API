<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentHistory;
use App\Models\Customer;
use App\Models\Order;
use App\Models\UserLog;
use App\Http\Resources\PaymentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Auth;
use Exception;

use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\PDF as Pdf;

class PaymentController extends Controller
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
            $customer_id = $request->customer_id;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $paymentQuery = Payment::where(function ($query) use ($keywords) {
                if ($keywords) {
                    $query->where('payment_type', 'like', '%' . $keywords . '%')
                        ->orWhereHas('order', function ($query) use ($keywords){
                            if($keywords) {
                                $query->where('sales_order_number', 'like', '%' . $keywords . '%')
                                 ->orWhere('transaction_number', 'like', '%' . $keywords . '%');
                            }
                        })
                        ->orWhereHas('customer', function ($query) use ($keywords){
                            if($keywords) {
                                $query->where('customer_name', 'like', '%' . $keywords . '%');
                            }
                        });
                }
            })
            ->where('payment_date', '>=', $start_date)
            ->where('payment_date', '<=', $end_date);
            
            if($customer_id != 0) {
                $paymentQuery->where('customer_id', $customer_id);
            }
    
            $payment = $paymentQuery->with('customer', 'order', 'user')
                                ->orderBy('id', 'DESC')
                                ->paginate();
            return PaymentResource::collection($payment);
                
        } catch (Exception $e) {
            
            if($e->getCode() == 0) {
                return response()->json(['message' => 'PAYMENTS NOT FOUND'], Response::HTTP_NOT_FOUND);
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
            $user = Auth::user();
            $data = $request->only('order_id', 'customer_id', 'payment_date', 'due_amount', 'payment_type', 'amount', 'remarks', 'status', 'current_amount');

            $payments = Payment::where('order_id', $request->order_id)->where('status', true)->get();

            if($payments->count() > 0) {

                return response()->json(['message' => 'Selected order has already payments'], Response::HTTP_BAD_REQUEST);
            }
    
            $payment = Payment::create([
                'order_id' => $request->order_id,
                'customer_id' => $request->customer_id,
                'user_id' => $user->id,
                'payment_date' => $request->payment_date,
                'due_amount' => $request->due_amount,
                'payment_type' => $request->payment_type,
                'amount' => $request->current_amount,
                'remarks' => $request->remarks,
                'status' => $request->status,
            ]);


            if($payment->amount > 0) {

                PaymentHistory::create([
                    'payment_id' => $payment->id,
                    'user_id' => $user->id,
                    'previous_payment' => $payment->amount,
                    'current_payment' => $payment->amount,
                    'date' => Carbon::now(),
                ]);
                
            }
    
            $customer = Customer::findOrFail($request->customer_id);
            UserLog::create([
                'user_id' => $user->id,
                'logs' => 'Payments',
                'remarks' => 'Create payment to '. $customer->customer_name. ' with the total amount of '. $request->due_amount,
                'date' => Carbon::now()->format('Y-m-d'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            $order = Order::find($payment->order_id);
            $order->status = ($request->amount >= $order->total_amount) ? true : false;
            $order->save();
            return new PaymentResource($payment->refresh());

        } catch (Exception $e) {
            if($e->getCode() == 0) {
                return response()->json(['message' => 'PAYMENTS NOT FOUND', $e->getMessage()], Response::HTTP_NOT_FOUND);
            } else
                return response()->json(['message' => 'SERVER ERROR', $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
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
            $payments = Payment::with('order', 'order.customer', 'order.order_details', 'order.order_details.product')->findOrFail($id);

            return new PaymentResource($payments);

        } catch (Exception $e) {
            
            if($e->getCode() == 0) {
                return response()->json(['message' => 'PAYMENTS NOT FOUND'], Response::HTTP_NOT_FOUND);
            } else
                return response()->json(['message' => 'SERVER ERROR', $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
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
            $payment = Payment::find($id);
            $payment->fill($request->all());
            $order = Order::find($payment->order_id);
    
            if($payment->amount >= $payment->due_amount) {
                $order->status = true;
                $order->save();
                $payment->status = true;
                $payment->save();
                return response()->json(['message' => 'PAYMENT HAS ALREADY FULLY PAID'], Response::HTTP_OK);
            } else {
                $payment->amount += $request->current_amount;
    
                if($payment->amount >= $payment->due_amount) {
                    $payment->amount = $payment->due_amount;
                    $payment->status = true;
                }
            }   
    
            if($order) {
    
                if($order->order_status == 'Cancel') {
                    return response()->json(['message' => 'UNABLE TO UPDATE CANCEL ORDER '. $order->status], Response::HTTP_BAD_REQUEST);
                }
    
                if($payment->amount >= $order->total_amount ) {
                    $order->status = true;
                    $order->save();
                } else {
                    $order->status = false;
                    $order->save();
                }
            }
    
            $customer = Customer::findOrFail($payment->customer_id);
    
            UserLog::create([
                'user_id' => $user->id,
                'logs' => 'Payments',
                'remarks' => 'Added payment '. $request->current_amount . ' to ' . $customer->customer_name . ' - ' . $order->transaction_number,
                'date' => Carbon::now()->format('Y-m-d'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
    
    
            if($request->current_amount > 0) {
                PaymentHistory::create([
                    'payment_id' => $payment->id,
                    'user_id' => Auth::user()->id,
                    'previous_payment' => $payment->amount,
                    'current_payment' => $request->current_amount,
                    'date' => Carbon::now(),
                ]);
            }
    
            $payment->save();
    
            return new PaymentResource($payment->refresh());
    
        } catch (Exception $e) {
            if($e->getCode() == 0) {
                return response()->json(['message' => 'PAYMENTS NOT FOUND'], Response::HTTP_NOT_FOUND);
            } else
                return response()->json(['message' => 'SERVER ERROR', $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
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
            $payment = Payment::findOrFail($id);

            $customer = Customer::findOrFail($payment->customer_id);

            $payment->delete();

            UserLog::create([
                'user_id' => $user->id,
                'logs' => 'Payments',
                'remarks' => 'Delete the payments of ' . $customer->customer_name,
                'date' => Carbon::now()->format('Y-m-d'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            return response()->noContent();

        } catch (Exception $e) {
            
            if($e->getCode() == 0) {
                return response()->json(['message' => 'PAYMENTS NOT FOUND'], Response::HTTP_NOT_FOUND);
            } else
                return response()->json(['message' => 'SERVER ERROR'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function generate_payment_report(Request $request) 
    {
        try {
            
             // Setting Request Body
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $showCancel = $request->cancel;
            $customer = $request->customer_id;

            $payments = Payment::with('order:id,customer_id,sales_order_number,total_amount,order_status', 'order.customer:id,customer_name', 'user:id,name')
            ->where('payment_date', '>=', $start_date)
            ->where('payment_date', '<=', $end_date);

            if(!$showCancel) {
                $payments->where('status', true);
            }

            if($customer > 0) {

                $payments->whereHas('order', function($query) use($customer){
                    $query->where('customer_id', $customer);
                });
            }

            $payments = $payments->get();
            $total_due = $payments->sum('due_amount');
            $total_amount = $payments->sum('amount');

            $pdf = PDF::loadView('pdf.payment-report', ['data' => $payments, 'start_date' => $start_date, 'end_date' => $end_date, 'total_due' => $total_due, 'total_amount' => $total_amount])->setPaper('a4', 'landscape');
            return $pdf->stream();

        } catch (Exception $e) {
            
            if($e->getCode() == 0) {
                return response()->json(['message' => 'PAYMENTS NOT FOUND', $e->getMessage()], Response::HTTP_NOT_FOUND);
            } else
                return response()->json(['message' => 'SERVER ERROR'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
