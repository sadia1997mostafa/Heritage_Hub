@extends('layouts.admin')
@section('title','Platform Revenue - Products')
@section('content')
<h1>Products contributing to Platform Revenue</h1>

<div class="card">
  <table style="width:100%;border-collapse:collapse">
    <tr><th>Product</th><th>Total Gross</th><th>Platform Fee</th><th># Earnings</th></tr>
    @foreach($products as $p)
      <tr style="border-bottom:1px solid #eee">
  <td style="padding:8px">{{ $p->product_title }}</td>
        <td style="padding:8px">৳ {{ number_format($p->total_gross,2) }}</td>
        <td style="padding:8px">৳ {{ number_format($p->total_platform_fee,2) }}</td>
        <td style="padding:8px">{{ $p->earnings_count }}</td>
      </tr>
    @endforeach
  </table>

  <div style="margin-top:10px">{{ $products->links() }}</div>
</div>

@endsection
