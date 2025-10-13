@extends('layouts.app')

@section('title','Order #'.$order->id)

@section('content')
<a href="{{ route('orders.index') }}">← Back to orders</a>

<h2>Order #{{ $order->id }}</h2>
<p>Placed: {{ $order->created_at->format('Y-m-d H:i') }}</p>
<p>Total: {{ number_format($order->total,2) }}</p>

<h3>Items</h3>
<ul>
  @foreach($order->items as $item)
    <li>{{ $item->product->name ?? 'Product' }} — Qty: {{ $item->qty }} — Price: {{ number_format($item->price,2) }}</li>
  @endforeach
</ul>

<h3>Shipments</h3>
<ul>
  @foreach($order->shipments as $s)
    <li>Vendor: {{ $s->vendor->store_name ?? 'Vendor' }} — Status: {{ $s->status }} @if($s->tracking_number) — Tracking: {{ $s->tracking_number }} @endif</li>
  @endforeach
</ul>

@endsection
