@extends('layout.app')

@section('content')
  <div style="width: 100%">
    <table style="width: 100%;">
      <thead>
        <tr>
            <td colspan="11">
              <div class="header-layout">
                <label class="title"> LMS Electrical Supply</label>
              </div>
            </td>
        </tr>
      </thead>
      <tbody>
          <tr>
            <td colspan="11" style="padding: 10px; font-style: bold;" class="text-center"> SALES ORDER ITEM SUMMARY REPORT </td>
          </tr>
          <tr>
            <td colspan="11" style="background-color: #333;"></td>
          </tr>
          <tr>
            <td colspan="6" style="font-style: bold"> DATE FROM :  {{ $start_date }}</td>
            <td colspan="6" style="font-style: bold"> DATE END : {{ $end_date }} </td>
          </tr>
          <tr>
            <td colspan="11" style="background-color: #333;"></td>
          </tr>
          <tr class="tr-total">
            <td style="width: 80px"> Date </td>
            <td> SO Number </td>
            <td> Customer</td>
            <td> Product Name </td>
            <td> Created By </td>
            <td> Status </td>
            <td> Quantity </td>
            <td> Unit </td>
            <td> Price </td>
            <td> Discount </td>
            <td> Total </td>
          </tr>
          <tr>
            <td colspan="11" style="background-color: #333;"></td>
          </tr>
        @foreach($data as $item) 
          <tr>
            <td style="width: 80px">{{ $item->order->sales_date }}</td>
            <td>{{ $item->order->sales_order_number }}</td>
            <td style="width: 150px">{{ $item->order->customer->customer_name  }}</td>
            <td style="width: 200px">{{ $item->product->product_name. ' '. $item->product->description. ' ('. $item->product->brands->description. ')'  }}</td>
            <td>{{ $item->order->user->name}}</td>
            <td>{{ $item->order->order_status }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ $item->product->unit }}</td>
            <td class="text-right">P {{ number_format($item->product->selling_price, 2) }}</td>
            <td class="text-right"> P {{ number_format($item->discount, 2) }}</td>
            <td class="text-right"> P {{ number_format($item->sub_total, 2) }}</td>
          </tr>
        @endforeach
          <tr>
            <td colspan="11" style="background-color: #333;"></td>
          </tr>
          <tr class="">
            <td colspan="6">TOTAL</td>
            <td colspan="1"> {{ $total_qty }}</td>
            <td colspan="2" class="text-right"> </td>
            <td colspan="1" class="text-right"> P {{ number_format($total_discount, 2) }} </td>
            <td colspan="1" class="text-right" style="color: red;"> P {{ number_format(($total - $total_discount), 2) }} </td>
          </tr>
      </tbody>
    </table>
  </div>

  @endsection