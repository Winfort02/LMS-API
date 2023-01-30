@extends('layout.app')

@section('content')
  <div style="width: 100%">
    <table style="width: 100%;">
      <thead>
        <tr>
            @if($report_type === 'STOCK RETURN')

            <td colspan="8">
              <div class="header-layout">
                <label class="title"> LMS Electrical Supply</label>
              </div>
            </td>

            @else
            <td colspan="7">
              <div class="header-layout">
                <label class="title"> LMS Electrical Supply</label>
              </div>
            </td>
            @endif
        </tr>
      </thead>
      <tbody>

          @if($report_type == 'STOCK RETURN')
            <tr>
              <td colspan="8" style="padding: 10px; font-style: bold;" class="text-center"> PRODUCT {{ $report_type }} INVENTORY REPORT </td>
            </tr>

            <tr>
              <td colspan="8" style="background-color: #333;"></td>
            </tr>
            <tr class="tr-total">
              <td> Date </td>
              <td> Plate Number / OR Number </td>
              <td> Product </td>
              <td> Supplier </td>
              <td> Quantity </td>
              <td> Unit </td>
              <td> Remarks </td>
              <td> Received By </td>
            </tr>
            <tr>
              <td colspan="8" style="background-color: #333;"></td>
            </tr>
            @foreach($data as $item)
              <tr>
                <td>{{ $item->date }}</td>
                <td>{{ $item->van_number }}</td>
                <td>{{ $item->product->product_name }}</td>
                <td>{{ $item->product->suppliers->supplier_name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->product->unit }}</td>
                <td>{{ $item->remarks }}</td>
                <td>{{ $item->user->name }} </td>
              </tr>
            @endforeach
            <tr class="">
              <td colspan="4"> TOTAL </td>
              <td colspan="1" style="color: red;"> {{ $total }} </td>
              <td colspan="3"></td>
            </tr>
          @else
            <tr>
              <td colspan="7" style="padding: 10px; font-style: bold;" class="text-center"> PRODUCT {{ $report_type }} INVENTORY REPORT </td>
            </tr>
            <tr>
              <td colspan="7" style="background-color: #333;"></td>
            </tr>
            <tr class="tr-total">
              <td> Date </td>
              <td> Plate Number / OR Number </td>
              <td> Product </td>
              <td> Supplier </td>
              <td> Quantity </td>
              <td> Unit </td>
              <td> Received By </td>
            </tr>
            <tr>
              <td colspan="8" style="background-color: #333;"></td>
            </tr>
            @foreach($data as $item)
              <tr>
                <td>{{ $item->date }}</td>
                <td>{{ $item->van_number }}</td>
                <td>{{ $item->product->product_name }}</td>
                <td>{{ $item->product->suppliers->supplier_name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->product->unit }}</td>
                <td>{{ $item->user->name }} </td>
              </tr>
            @endforeach
            <tr class="">
              <td colspan="4"> TOTAL </td>
              <td colspan="1" style="color: red;"> {{ $total }} </td>
              <td colspan="2"></td>
            </tr>
          @endif
      </tbody>
    </table>
  </div>

  @endsection