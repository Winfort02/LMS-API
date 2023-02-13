<?php

namespace App\Http\Controllers;

use App\Models\Customer;
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
    public function generate_dashboard_reports() {

        $data = [];
        $current_date = Carbon::now()->timezone('Asia/Manila')
        ->format('Y-m-d');

        $customers = Customer::where('is_active', true)->get();
        $suppliers = Supplier::where('is_active', true)->get();
        $categories = Category::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();
        $sales = Order::where('sales_date', $current_date)->where('order_status', 'Completed')->get();
        $cash_sales = Order::where('sales_date', $current_date)->where('sales_type', 'CASH')->where('order_status', 'Completed')->get();
        $charge_sales = Order::where('sales_date', $current_date)->where('sales_type', 'CHARGE')->where('order_status', 'Completed')->get();
        $delivery_sales = Order::where('sales_date', $current_date)->where('sales_type', 'DELIVERY')->where('order_status', 'Completed')->get();

        $cash =  ($cash_sales->count() > 0) ? $cash_sales->sum('total_amount') : 0;
        $delivery =  ($delivery_sales->count() > 0) ? $delivery_sales->sum('total_amount') : 0;
        $charge =  ($charge_sales->count() > 0) ? $charge_sales->sum('total_amount') : 0;

        $data = [
            'products' => ($products->count() > 0 ) ? $products->count() : 0,
            'categories' => ($categories->count() > 0) ? $categories->count() : 0,
            'suppliers' => ($suppliers->count() > 0) ? $suppliers->count() : 0,
            'customers' => ($customers->count() > 0) ? $customers->count() : 0, 
            'cash_sales' => $cash,
            'charge_sales' => $charge,
            'delivery_sales' => $delivery,
            'current_sales' => $cash + $charge + $delivery
        ];

        return  response()->json(['data' =>  $data], Response::HTTP_OK);
    }

    public function get_by_month($object, $value)
    {

        $pallet = 0;

        $this->val = $value;
        $filtered_item = array_filter(
            $object,
            function ($obj) {
                return $obj->month == $this->val;
            }
        );

        foreach ($filtered_item as $item) {
            $pallet += $item->total_sales;
        }
        return $pallet;
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

        $cancel_sales = json_decode($cancel);
        $complete_sales = json_decode($completed);

        $u_jan = $this->get_by_month($cancel_sales, '01');
        $u_feb = $this->get_by_month($cancel_sales, '02');
        $u_mar = $this->get_by_month($cancel_sales, '03');
        $u_apr = $this->get_by_month($cancel_sales, '04');
        $u_may = $this->get_by_month($cancel_sales, '05');
        $u_jun = $this->get_by_month($cancel_sales, '06');
        $u_jul = $this->get_by_month($cancel_sales, '07');
        $u_aug = $this->get_by_month($cancel_sales, '08');
        $u_sep = $this->get_by_month($cancel_sales, '09');
        $u_oct = $this->get_by_month($cancel_sales, '10');
        $u_nov = $this->get_by_month($cancel_sales, '11');
        $u_dec = $this->get_by_month($cancel_sales, '12');


        $jan = $this->get_by_month($complete_sales, '01');
        $feb = $this->get_by_month($complete_sales, '02');
        $mar = $this->get_by_month($complete_sales, '03');
        $apr = $this->get_by_month($complete_sales, '04');
        $may = $this->get_by_month($complete_sales, '05');
        $jun = $this->get_by_month($complete_sales, '06');
        $jul = $this->get_by_month($complete_sales, '07');
        $aug = $this->get_by_month($complete_sales, '08');
        $sep = $this->get_by_month($complete_sales, '09');
        $oct = $this->get_by_month($complete_sales, '10');
        $nov = $this->get_by_month($complete_sales, '11');
        $dec = $this->get_by_month($complete_sales, '12');

        $label = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $completed_sale = [
            $jan,
            $feb,
            $mar,
            $apr,
            $may,
            $jun,
            $jul,
            $aug,
            $sep,
            $oct,
            $nov,
            $dec
        ];
        $cancel_sale = [
            $u_jan,
            $u_feb,
            $u_mar,
            $u_apr,
            $u_may,
            $u_jun,
            $u_jul,
            $u_aug,
            $u_sep,
            $u_oct,
            $u_nov,
            $u_dec
        ];

        $chartData = [
            'label' => $label,
            'cancel' => $cancel_sale,
            'completed' => $completed_sale,
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
    
            return  response()->json(['data' =>  $data], Response::HTTP_OK);

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
    
            return  response()->json(['data' =>  $data], Response::HTTP_OK);

        }
       
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
