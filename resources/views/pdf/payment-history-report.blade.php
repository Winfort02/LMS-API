@extends('layout.app')

@section('content')
  <div style="width: 100%">
    <table style="width: 100%;">
      <thead>
        <tr>
            <td colspan="8">
              <div class="header-layout">
                <label class="title"> LMS Electrical Supply</label>
              </div>
            </td>
        </tr>
      </thead>
      <tbody>
          <tr>
            <td colspan="8" style="padding: 10px; font-style: bold;" class="text-center"> PAYMENT HISTORY SUMMARY REPORT </td>
          </tr>
          @if(count($data) > 0)
            <tr>
              <td> Customer Name :</td>
              <td colspan="2"> {{ $data[0]->payment->order->customer->customer_name }}</td>
              <td> Address :</td>
              <td colspan="2"> {{ $data[0]->payment->order->customer->address }}</td>
              <td> Phone # :</td>
              <td colspan="1"> {{ $data[0]->payment->order->customer->phone_number }}</td>
            </tr>
          @else
            <tr>
              <td colspan="8"></td>
            </tr>
          @endif
          <tr>
            <td colspan="8" style="background-color: #333;"></td>
          </tr>
          <tr>
            <td style="font-style: bold;"> DATE FROM : </td>
            <td colspan="3" style="font-style: bold;"> {{ $start_date }} </td>
            <td style="font-style: bold;"> DATE END : </td>
            <td colspan="3" style="font-style: bold;"> {{ $end_date }} </td>
          </tr>
          <tr>
            <td colspan="8" style="background-color: #333;"></td>
          </tr>
          <tr class="tr-total">
            <td> Date </td>
            <td> SO Number </td>
            <td> Customer</td>
            <td> Status </td>
            <td> Encoded By </td>
            <td> Due Amount</td>
            <td> Amount </td>
            <td> Balance </td>
          </tr>
          <tr>
            <td colspan="8" style="background-color: #333;"></td>
          </tr>
        @foreach($data as $index => $item) 
          @if(!$item->payment->status)
            <tr style="background-color: red; color: white;">
              <td>{{ $item->date }}</td>
              <td>{{ $item->payment->order->sales_order_number }}</td>
              <td>{{ $item->payment->order->customer->customer_name  }}</td>
              <td class="text-right">{{ $item->payment->status ? 'Active' : 'Cancel' }} - {{ ($item->payment->due_amount - $item->previous_payment) > 0 ? '( UNPAID )' : '( PAID )' }}</td>
              <td>{{ $item->user->name }}</td>
                <td class="text-right">P {{ ($item->payment->due_amount + $item->current_payment) - $item->previous_payment < 0 ? '0.00' : number_format(($item->payment->due_amount + $item->current_payment) - $item->previous_payment, 2) }} </td>
              <td class="text-right">P {{ number_format($item->current_payment, 2) }} </td>
              <td class="text-right">P {{ number_format(($item->payment->due_amount - $item->previous_payment) > 0 ? $item->payment->due_amount - $item->previous_payment : 0, 2) }}</td>
            </tr>

          @else

            <tr>
                <td>{{ $item->date }}</td>
                <td>{{ $item->payment->order->sales_order_number }}</td>
                <td>{{ $item->payment->order->customer->customer_name  }}</td>
                <td class="text-right">{{ $item->payment->status ? 'Active' : 'Cancel' }} - {{ ($item->payment->due_amount - $item->previous_payment) > 0 ? '( UNPAID )' : '( PAID )' }}</td>
                <td>{{ $item->user->name }}</td>
                <td class="text-right">P {{ ($item->payment->due_amount + $item->current_payment) - $item->previous_payment < 0 ? '0.00' : number_format(($item->payment->due_amount + $item->current_payment) - $item->previous_payment, 2) }} </td>
                <td class="text-right">P {{ number_format($item->current_payment, 2) }} </td>
                <td class="text-right">P {{ number_format(($item->payment->due_amount - $item->previous_payment) > 0 ? $item->payment->due_amount - $item->previous_payment : 0, 2) }}</td>
            </tr>
          @endif
        @endforeach

        <tr>
            <td colspan="8" style="background-color: #333;"></td>
          </tr>
          <tr class="">
            <td>TOTAL : </td>
            <td colspan="5" class="text-right" style="color: red;"> P {{ $total_due ? number_format($total_due, 2) : '0.00' }} </td>
            <td class="text-right" style="color: red;"> P {{ number_format($current, 2) }} </td>
            <td class="text-right" style="color: red;"> P {{ number_format($total_due - $current, 2) }} </td>
          </tr>
          
      </tbody>
    </table>
  </div>

  @endsection