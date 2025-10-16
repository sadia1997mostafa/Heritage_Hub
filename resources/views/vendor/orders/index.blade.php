@extends('layouts.vendor')
@section('title','Vendor Orders')
@push('styles') @vite(['resources/css/vendor.css']) @endpush
@section('content')
<div class="hh-container pad-section">
  <h2>Your Orders</h2>
  @if($shipments->count())
    <div class="vendor-orders">
      @foreach($shipments as $s)
        <a class="ship-card" href="{{ route('vendor.orders.show', $s->id) }}">
          <div class="ship-head">
            <div>#{{ $s->id }}</div>
            <div class="muted">Order #: {{ $s->order_id }} • {{ $s->created_at->diffForHumans() }}</div>
          </div>
          <div class="ship-body">
            <div>{{ $s->order->shipping_address['name'] ?? 'Customer' }}</div>
            <div class="muted">Items: {{ $s->order->items->count() }} • Total: ৳ {{ number_format($s->order->total ?? 0,2) }}</div>
          </div>
          <div class="ship-foot">Status: <strong>{{ $s->status }}</strong></div>
        </a>
      @endforeach
    </div>
    {{ $shipments->links() }}
  @else
    <p class="muted">No orders yet.</p>
  @endif
</div>
@endsection
