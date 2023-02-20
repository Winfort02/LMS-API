<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentHistory;
use App\Models\Customer;
use App\Models\Order;
use App\Models\UserLog;
use App\Http\Resources\PaymentHistoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Auth;
use Exception;

use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\PDF as Pdf;

class PaymentHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

        /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show_all_payment_history_by_payment($id)
    {
        try {
            
            $payments = PaymentHistory::where('payment_id', $id)->with(['payment', 'payment.order', 'user'])->orderBy('id', 'DESC')->get();

            return new PaymentHistoryResource($payments);

        } catch (Exception $e) {
            
            if($e->getCode() == 0) {
                return response()->json(['message' => 'PAYMENTS NOT FOUND', $e->getMessage()], Response::HTTP_NOT_FOUND);
            } else
                return response()->json(['message' => 'SERVER ERROR', $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        //
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



    public function generate_customer_payment_history(Request $request)
    {
        try {
            
            // Setting up the request body

            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $customer_id = $request->customer_id;
            $cancel = $request->cancel;

            $payments = PaymentHistory::whereHas('payment', function ($query) use ($request) {
                $query->where('status', $request->cancel);
            })->whereHas('payment.order', function ($query) use ($request) {
                $query->where('customer_id', $request->customer_id);
            })
            ->with(['payment', 'payment.order', 'payment.order.order_details', 'payment.order.customer', 'user'])
            ->where('date', '>=', $start_date)
            ->where('date', '<=', $end_date)
            ->orderBy('created_at', 'DESC')
            ->get();

            $totalPayments = Payment::where('status', $request->cancel)
            ->where('payment_date', '>=', $start_date)
            ->where('payment_date', '<=', $end_date)
            ->whereHas('order', function ($query) use ($request) {
                $query->where('customer_id', $request->customer_id);
            })
            ->whereHas('payment_history')
            ->selectRaw('SUM(due_amount) as total_payment_due')
            ->first();

            $total_payment = $payments->sum('current_payment');

            $pdf = PDF::loadView('pdf.payment-history-report', ['data' => $payments, 'start_date' => $start_date, 'end_date' => $end_date, 'total_due' => $totalPayments->total_payment_due, 'current' => $total_payment])->setPaper('a4', 'landscape');
            return $pdf->stream();

        } catch (Exception $e) {
            
            if($e->getCode() == 0) {
                return response()->json(['message' => 'PAYMENTS NOT FOUND', $e->getMessage()], Response::HTTP_NOT_FOUND);
            } else
                return response()->json(['message' => 'SERVER ERROR', $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
