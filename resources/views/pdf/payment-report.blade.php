@extends('layout.app')

@section('content')
  <div style="width: 100%">
    <table style="width: 100%;">
      <thead>
        <tr>
            <td colspan="7">
              <div class="header-layout">
                <label class="title"> LMS Electrical Supply</label>
              </div>
            </td>
        </tr>
      </thead>
      <tbody>
          <tr>
            <td colspan="7" style="padding: 10px; font-style: bold;" class="text-center"> PAYMENT SUMMARY REPORT </td>
          </tr>
          <tr>
            <td colspan="7" style="background-color: #333;"></td>
          </tr>
          <tr>
            <td style="font-style: bold;"> DATE FROM : </td>
            <td colspan="2" style="font-style: bold;"> {{ $start_date }} </td>
            <td style="font-style: bold;"> DATE END : </td>
            <td colspan="3" style="font-style: bold;"> {{ $end_date }} </td>
          </tr>
          <tr>
            <td colspan="7" style="background-color: #333;"></td>
          </tr>
          <tr class="tr-total">
            <td> Date </td>
            <td> SO Number </td>
            <td> Customer</td>
            <td> Payment Status </td>
            <td> Due Amount </td>
            <td> Amount </td>
            <td> Balance </td>
          </tr>
          <tr>
            <td colspan="7" style="background-color: #333;"></td>
          </tr>
        @foreach($data as $item) 
          @if(!$item->status)

            <tr style="background-color: red; color: white;">
              <td>{{ $item->payment_date }}</td>
              <td>{{ $item->order->sales_order_number }}</td>
              <td>{{ $item->order->customer->customer_name  }}</td>
              <td>{{ $item->status ? 'Active' : 'Cancel' }} - {{ ($item->due_amount - $item->amount) > 0 ? '( UNPAID )' : '( PAID )' }}</td>
              <td class="text-right">P {{ number_format($item->due_amount, 2) }}</td>
              <td class="text-right">P {{ number_format($item->amount, 2) }}</td>
              <td class="text-right">P {{ number_format(($item->due_amount - $item->amount) > 0 ? $item->due_amount - $item->amount : 0, 2) }}</td>
            </tr>

          @else

            <tr>
              <td>{{ $item->payment_date }}</td>
              <td>{{ $item->order->sales_order_number }}</td>
              <td>{{ $item->order->customer->customer_name  }}</td>
              <td>{{ $item->status ? 'Active' : 'Cancel' }} - {{ ($item->due_amount - $item->amount) > 0 ? '( UNPAID )' : '( PAID )' }}</td>
              <td class="text-right">P {{ number_format($item->due_amount, 2) }}</td>
              <td class="text-right">P {{ number_format($item->amount, 2) }}</td>
              <td class="text-right">P {{ number_format(($item->due_amount - $item->amount) > 0 ? $item->due_amount - $item->amount : 0, 2) }}</td>
            </tr>
          @endif
        @endforeach
          <tr>
            <td colspan="7" style="background-color: #333;"></td>
          </tr>
          <tr class="">
            <td>TOTAL : </td>
            <td colspan="4" class="text-right" style="color: red;"> P {{ number_format($total_due, 2) }} </td>
            <td class="text-right" style="color: red;">P {{ number_format($total_amount, 2) }}</td>
            <td class="text-right" style="color: red;">P {{ number_format(($total_due - $total_amount) > 0 ? $total_due - $total_amount : 0, 2) }}</td>
          </tr>
      </tbody>
    </table>
  </div>

  @endsection