@extends('layout.app')

@section('content')
  <div style="width: 100%">
    <table style="width: 100%;">
      <thead>
        <tr>
            <td colspan="13">
              <div class="header-layout">
                <label class="title"> LMS Electrical Supply</label>
              </div>
            </td>
        </tr>
      </thead>
      <tbody>
          <tr>
            <td colspan="13" style="padding: 10px;" class="text-center"> PRODUCT INVENTORY REPORT </td>
          </tr>
          <tr>
            <td colspan="13" style="background-color: #333;"></td>
          </tr>
          <tr class="tr-total">
            <td style="width: 200px;"> Product </td>
            <td> Description </td>
            <td> Supplier </td>
            <td colspan="2" class="text-center"> Stock In</td>
            <td colspan="2" class="text-center"> Stock Return</td>
            <td colspan="2" class="text-center"> Sold Out</td>
            <td colspan="2" class="text-center"> Stock On Hand</td>
            <td> Unit Price </td>
            <td> Status </td>
          </tr>
          <tr>
            <td colspan="13" style="background-color: #333;"></td>
          </tr>
          @foreach($data as $item)
            <tr>
              <td>{{ $item->product_name }}</td>
              <td>{{ $item->description }}</td>
              <td>{{ $item->suppliers->supplier_name }}</td>

              @if(count($item->product_stock_in) > 0)
                @foreach($item->product_stock_in as $product)
                  <td class="text-center">{{ $product->quantity }}</td>
                  <td class="text-center">{{ $item->unit }}</td>
                @endforeach
              @else
                <td class="text-center"> 0 </td>
                <td class="text-center"> - </td>
              @endif

              @if(count($item->product_stock_return) > 0)
                @foreach($item->product_stock_return as $product)
                  <td class="text-center">{{ $product->quantity }} </td>
                  <td class="text-center">{{ $item->unit }} </td>
                @endforeach
              @else
                <td class="text-center"> 0 </td>
                <td class="text-center"> - </td>
              @endif

              @if(count($item->order_details) > 0)
                @foreach($item->order_details as $product)
                  <td class="text-center">{{ $product->quantity }} </td>
                  <td class="text-center">{{ $item->unit }} </td>
                @endforeach
              @else
                <td class="text-center"> 0 </td>
                <td class="text-center"> - </td>
              @endif

              <td class="text-center">{{ $item->quantity }}</td>
              <td class="text-center">{{ $item->unit }}</td>

              <td class="text-right">P {{ number_format($item->selling_price, 2)}}</td>

              @if($item->quantity <= 0)
                <td class="text-danger text-center"> Out Of Stock </td>
              @else
                <td class="text-success text-center"> Available </td>
              @endif
            </tr>
          @endforeach
      </tbody>
    </table>
  </div>

  @endsection