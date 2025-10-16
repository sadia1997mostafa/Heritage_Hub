@extends('layouts.vendor')
@section('title','Order #'.$shipment->id)
@push('styles') @vite(['resources/css/vendor.css']) @endpush
@section('content')
<div class="hh-container pad-section">
  <a href="{{ route('vendor.orders.index') }}">← Back to orders</a>
  <h2>Shipment #{{ $shipment->id }}</h2>
  <div class="ship-detail">
    <div class="left">
      <h4>Customer</h4>
      <div>{{ $shipment->order->shipping_address['name'] ?? '' }}</div>
      <div class="muted">{{ $shipment->order->shipping_address['phone'] ?? '' }}</div>
      <h4>Items</h4>
      <ul>
        @foreach($shipment->order->items as $it)
          <li>{{ $it->product->title ?? 'Product' }} — Qty: {{ $it->qty }} — ৳ {{ number_format($it->price,2) }}</li>
        @endforeach
      </ul>
    </div>

    <aside class="right">
      <h4>Shipment</h4>
      <div>Status: <strong>{{ $shipment->status }}</strong></div>
      <form method="POST" action="{{ route('vendor.orders.update', $shipment->id) }}">@csrf
        <label>Set status
          <select name="status">
            <option value="processing" {{ $shipment->status==='processing'?'selected':'' }}>Processing</option>
            <option value="shipped" {{ $shipment->status==='shipped'?'selected':'' }}>Shipped</option>
            <option value="delivered" {{ $shipment->status==='delivered'?'selected':'' }}>Delivered</option>
          </select>
        </label>
        <label>Tracking number <input name="tracking_number" value="{{ $shipment->tracking_number }}" /></label>
        <button class="btn-primary" type="submit">Update</button>
      </form>

      <div style="margin-top:12px;display:flex;gap:8px;">
        <a class="btn-ghost" href="{{ route('vendor.orders.packing-slip', $shipment->id) }}" target="_blank">Print packing slip</a>
        <form method="POST" action="{{ route('vendor.orders.quickship', $shipment->id) }}">@csrf
          <button type="submit" class="btn-ghost">Mark shipped</button>
        </form>
      </div>
    </aside>
  </div>
</div>
@endsection
