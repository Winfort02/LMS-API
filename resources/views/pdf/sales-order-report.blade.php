@extends('layout.app')

@section('content')
  <div style="width: 100%">
    <table style="width: 100%;">
      <thead>
        <tr>
            <td colspan="6">
              <div class="header-layout">
                <label class="title"> LMS Electrical Supply</label>
              </div>
            </td>
        </tr>
      </thead>
      <tbody>
          <tr>
            <td colspan="6" style="padding: 10px; font-style: bold;" class="text-center"> SALES ORDER SUMMARY REPORT </td>
          </tr>
          <tr>
            <td colspan="6" style="background-color: #333;"></td>
          </tr>
          <tr>
            <td style="font-style: bold;"> DATE FROM : </td>
            <td colspan="2" style="font-style: bold;"> {{ $start_date }} </td>
            <td style="font-style: bold;"> DATE END : </td>
            <td colspan="2" style="font-style: bold;"> {{ $end_date }} </td>
          </tr>
          <tr>
            <td colspan="6" style="background-color: #333;"></td>
          </tr>
          <tr class="tr-total">
            <td> Date </td>
            <td> SO Number </td>
            <td> Customer</td>
            <td> Payment Type </td>
            <td> Status </td>
            <td> Total Sales </td>
          </tr>
          <tr>
            <td colspan="6" style="background-color: #333;"></td>
          </tr>
        @foreach($data as $item) 
          <tr>
            <td>{{ $item->sales_date }}</td>
            <td>{{ $item->sales_order_number }}</td>
            <td>{{ $item->customer->customer_name  }}</td>
            <td>{{ $item->payment_type }}</td>
            @if($item->order_status == 'Completed')
              <td class="text-success"> Completed </td>
            @else
              <td class="text-danger"> Cancel </td>
            @endif
            <td class="text-right">P {{ number_format($item->total_amount, 2) }}</td>
          </tr>
        @endforeach
          <tr>
            <td colspan="6" style="background-color: #333;"></td>
          </tr>
          <tr class="">
            <td>TOTAL SALES : </td>
            <td colspan="5" class="text-right" style="color: red;"> P {{ number_format($total_cash, 2) }} </td>
          </tr>
      </tbody>
    </table>
  </div>

  @endsection