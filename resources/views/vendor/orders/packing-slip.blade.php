@extends('layouts.blank')
@section('title','Packing Slip #'.$shipment->id)
@section('content')
<div class="packing-slip" style="font-family:Arial,Helvetica,sans-serif;max-width:800px;margin:0 auto;padding:20px;color:#111;">
  <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
    <div>
      <h2 style="margin:0">Packing Slip</h2>
      <div style="color:#666">Order #{{ $shipment->order->id }} · Shipment #{{ $shipment->id }}</div>
    </div>
    <div style="text-align:right">
      <strong>{{ config('app.name') }}</strong>
      <div style="color:#666">{{ url('/') }}</div>
    </div>
  </header>

  <section style="display:flex;justify-content:space-between;gap:20px;margin-bottom:20px;">
    <div style="flex:1">
      <h4 style="margin:0 0 8px">Ship To</h4>
      <div>{{ $shipment->order->shipping_address['name'] ?? '' }}</div>
      <div>{{ $shipment->order->shipping_address['address'] ?? '' }}</div>
      <div>{{ $shipment->order->shipping_address['phone'] ?? '' }}</div>
    </div>
    <div style="flex:1">
      <h4 style="margin:0 0 8px">Vendor</h4>
      <div>{{ $shipment->order->items->first()->product->vendor->shop_name ?? 'Vendor' }}</div>
      <div style="color:#666">Shipment Date: {{ $shipment->created_at->format('Y-m-d') }}</div>
    </div>
  </section>

  <table style="width:100%;border-collapse:collapse;margin-bottom:20px">
    <thead>
      <tr>
        <th style="text-align:left;border-bottom:1px solid #ddd;padding:8px">Product</th>
        <th style="text-align:right;border-bottom:1px solid #ddd;padding:8px">Qty</th>
      </tr>
    </thead>
    <tbody>
      @foreach($shipment->order->items as $it)
      <tr>
        <td style="padding:8px;border-bottom:1px solid #f3f3f3">{{ $it->product->title ?? 'Product' }}</td>
        <td style="padding:8px;text-align:right;border-bottom:1px solid #f3f3f3">{{ $it->qty }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <footer style="color:#666;font-size:13px">Generated on {{ now()->toDateTimeString() }} — please detach this slip and place inside the parcel.</footer>
</div>
@endsection

@push('styles')
<style media="print">
  body{background:#fff;color:#000}
  .packing-slip{max-width:794px}
  @page{size:A4;margin:20mm}
</style>
@endpush
