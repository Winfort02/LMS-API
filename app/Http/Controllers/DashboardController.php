<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class DashboardController extends Controller
{
    public function generate_dashboard_reports(Request $request) {

        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $data = [];
        // $yesterday_date = Carbon::yesterday()->timezone('Asia/Manila')
        // ->format('Y-m-d');
        $current_date = Carbon::now()->timezone('Asia/Manila')
        ->format('Y-m-d');

        $customers = Customer::where('is_active', true)->get();
        $suppliers = Supplier::where('is_active', true)->get();
        $categories = Category::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();
        $sales = Order::where('sales_date', $start_date)->where('order_status', 'Completed')->get();
        $cash_sales = Order::where('sales_type', 'CASH')->where('sales_date', $start_date)->where('order_status', 'Completed')->get();
        $charge_sales = Order::where('sales_type', 'CHARGE')->where('sales_date', $start_date)->where('order_status', 'Completed')->get();
        $delivery_sales = Order::where('sales_type', 'DELIVERY')->where('sales_date', $start_date)->where('order_status', 'Completed')->get();

        $cash =  ($cash_sales->count() > 0) ? $cash_sales->sum('total_amount') : 0;
        $delivery =  ($delivery_sales->count() > 0) ? $delivery_sales->sum('total_amount') : 0;
        $charge =  ($charge_sales->count() > 0) ? $charge_sales->sum('total_amount') : 0;
        $sales_sales =  ($sales->count() > 0) ? $sales->sum('total_amount') : 0;

        $data = [
            'products' => ($products->count() > 0 ) ? $products->count() : 0,
            'categories' => ($categories->count() > 0) ? $categories->count() : 0,
            'suppliers' => ($suppliers->count() > 0) ? $suppliers->count() : 0,
            'customers' => ($customers->count() > 0) ? $customers->count() : 0, 
            'cash_sales' => $cash,
            'charge_sales' => $charge,
            'delivery_sales' => $delivery,
            'current_sales' => $sales_sales,
        ];

        return  response()->json(['data' =>  $data], Response::HTTP_OK);
    }

    public function get_by_month($object, $value)
    {

        $total = 0;

        $this->val = $value;
        $filtered_item = array_filter(
            $object,
            function ($obj) {
                return $obj->month == $this->val;
            }
        );

        foreach ($filtered_item as $item) {
            $total += $item->total_sales;
        }
        return $total;
    }

    public function generate_yearly_sales_dashboard($year) {

        $cancel = Order::select(DB::raw("(sum(total_amount)) as total_sales"), DB::raw("(DATE_FORMAT(sales_date, '%m')) as month"))
            ->whereYear('sales_date', $year)
            ->where('order_status', 'Cancel')
            ->groupBy(DB::raw("DATE_FORMAT(sales_date, '%m')"))
            ->get();
    
        $completed = Order::select(DB::raw("(sum(total_amount)) as total_sales"), DB::raw("(DATE_FORMAT(sales_date, '%m')) as month"))
        ->whereYear('sales_date', $year)
        ->where('order_status', 'Completed')
        ->groupBy(DB::raw("DATE_FORMAT(sales_date, '%m')"))
        ->get();

        $payment = Payment::select(DB::raw("(sum(amount)) as total_sales"), DB::raw("(DATE_FORMAT(payment_date, '%m')) as month"))
        ->whereYear('payment_date', $year)
        ->where('status', true)
        ->groupBy(DB::raw("DATE_FORMAT(payment_date, '%m')"))
        ->get();

        $balance = Payment::select(DB::raw("(sum(due_amount)) as total_sales"), DB::raw("(DATE_FORMAT(payment_date, '%m')) as month"))
        ->whereYear('payment_date', $year)
        ->where('status', false)
        ->groupBy(DB::raw("DATE_FORMAT(payment_date, '%m')"))
        ->get();
    
        $cancel_sales = json_decode($cancel);
        $complete_sales = json_decode($completed);
        $paid_sales = json_decode($payment);
        $unpaid_sales = json_decode($balance);

        $months = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
        $label = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $cancel_sale = [];
        $completed_sale = [];
        $paid_sale = [];
        $unpaid_sale = [];
    
        foreach ($months as $month) {
            array_push($cancel_sale, $this->get_by_month($cancel_sales, $month));
            array_push($completed_sale, $this->get_by_month($complete_sales, $month));
            array_push($paid_sale, $this->get_by_month($paid_sales, $month));
            array_push($unpaid_sale, $this->get_by_month($unpaid_sales, $month));
        }
    
        $chartData = [
            'label' => $label,
            'cancel' => $cancel_sale,
            'completed' => $completed_sale,
            'paid' => $paid_sale,
            'balance' => $unpaid_sale,
        ];
    
        return  response()->json(['data' =>  $chartData], Response::HTTP_OK);
    }

    public function generate_product_status_report($product_id) {
        $data = [];
        if($product_id == 0) {
            $product_on_hand = Product::where('is_active', 1)->where('quantity', '>', 0)->get();
            $product_out_of_stock = Product::where('is_active', 1)->where('quantity', '<=', 0)->get();
            $data = [
                'label' => ['ON HAND', 'OUT OF STOCK'],
                'on_hand' => $product_on_hand->count(),
                'out_of_stock' => $product_out_of_stock->count(),
            ];
        } else {
            $product = Product::where('id', $product_id)->get();
            $order = OrderDetail::where('product_id', $product_id)->whereHas('order', function($query){
                $query->where('order_status', 'Completed');
            })->get();
            $data = [
                'label' => ['ON HAND', 'PRODUCT SOLD'],
                'on_hand' => ($product) ? $product->sum('quantity') : 0,
                'out_of_stock' => ($order) ? $order->sum('quantity') : 0,
            ];
        }
        return  response()->json(['data' =>  $data], Response::HTTP_OK);
    }

    public function show_latest_ten_transaction() {

        $sales = Order::with('customer', 'user', 'order_details')->orderBy('created_at', 'DESC')->take(10)->get();

        return OrderResource::collection($sales);
    }


    public function show_product_with_low_quantity() {

        $products = Product::with('brands:id,brand_name')->where('quantity', '<=', 10)->where('is_active', true)->orderBy('quantity', 'DESC')->get();

        return ProductResource::collection($products);
    }
}
