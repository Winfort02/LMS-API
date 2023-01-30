@extends('layout.app')

@section('content')
  <div style="width: 100%">
    <table style="width: 100%;">
      <thead>
        <tr>
            <td colspan="5">
              <div class="header-layout">
                <label class="title"> LMS Electrical Supply</label>
              </div>
            </td>
        </tr>
        <tr>
          <td colspan="5" style="background-color: #333;"></td>
        </tr>
        <tr>
          <td colspan="1" style="padding: 5px; text-transform: uppercase;"> CUSTOMER NAME : </td>
          <td colspan="2"> {{ $data->customer->customer_name }} </td>
          <td style="text-transform: uppercase;"> SO DATE :</td>
          <td colspan="1">{{ $data->sales_date }}</td>
        </tr>
        <tr>
          <td style="text-transform: uppercase;"> SO NUMBER : {{ $data->sales_order_number  }}</td>
          <td style="text-transform: uppercase;"> TRANSACTION NUMBER :  {{ $data->transaction_number }}</td>
          <td style="text-transform: uppercase;"> ORDER STATUS :  {{ $data->order_status  }} </td>
          <td style="text-transform: uppercase;" colspan="2"> PAYMENT STATUS :  {{ $data->status ? 'PAID' : 'PENDING'}} </td>
        </tr>
        <tr>
          <td style="text-transform: uppercase;"> PAYMENT TYPE :  {{ $data->payment_type }}</td>
          <td style="text-transform: uppercase;"> ADDRESS:  {{ $data->customer->address }}  </td>
          <td style="text-transform: uppercase;"> CONTACT NUMBER: {{ $data->customer->phone_number }} </td>
          <td style="text-transform: uppercase;" colspan="2"> BALANCE: P {{ number_format(($data->total_amount - $data->payment) , 2) }}  </td>
        </tr>
        <tr>
          <td> REMARKS : </td>
          <td colspan="4" style="text-transform: uppercase;"> {{ $data->remarks }}</td>
        </tr>
      
      </thead>
      <tbody>
        <tr>
          <td colspan="5" style="background-color: #333;"></td>
        </tr>
        <tr class="tr-total">
          <td> Product </td>
          <td> Quantity </td>
          <td> Unit Price  </td>
          <td> Discount</td>
          <td> Sub Total  </td>
        </tr>
        <tr>
          <td colspan="5" style="background-color: #333;"></td>
        </tr>

        @foreach($data->order_details as $item) 
          <tr>
            <td>{{ $item->product->product_name }}</td>
            <td>{{ $item->quantity }}</td>
            <td style="text-align: right;">P {{ number_format($item->price, 2) }}</td>
            <td style="text-align: right;">P {{ number_format($item->discount, 2) }}</td>
            <td style="text-align: right;">P {{ number_format(($item->price * $item->quantity), 2) }}</td>
          </tr>
        @endforeach
        <tr>
          <td colspan="5" style="background-color: #333;"></td>
        </tr>
        <tr>
          <td colspan="3" class="text-right"> Sub Total </td>
          <td colspan="2" style="text-align: right;"> P {{ number_format(($total), 2) }} </td>
        </tr>
        <tr>
          <td colspan="3" class="text-right"> Total Discount </td>
          <td colspan="2" style="text-align: right;"> P {{ number_format($total_discount, 2) }} </td>
        </tr>
        <tr>
          <td colspan="3" class="text-right"> Cash Rendered </td>
          <td colspan="2" style="text-align: right;"> P {{ number_format($data->payment, 2) }} </td>
        </tr>
        <tr>
          <td colspan="5" style="background-color: #333;"></td>
        </tr>
        <tr class="">
          <td colspan="3" class="text-right"> Total </td>
          <td colspan="2" style="text-align: right; color: red;"> P {{ number_format(($total - $total_discount), 2) }} </td>
        </tr>
      </tbody>
    </table>

    <div style="margin-top: 3rem;">
      <div style="margin-left: 10px">____________________________</div>
      <label style="font-size: 14px; padding-left: 4.5rem"> Customer's Signature </label><br><br>
    </div>


  </div>

  @endsection