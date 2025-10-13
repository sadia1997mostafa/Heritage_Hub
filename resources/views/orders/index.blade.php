@extends('layouts.app')

@section('title','Your Orders')

@section('content')
<h2>Your Orders</h2>

@if($orders->count())
  <ul class="orders-list">
    @foreach($orders as $order)
      <li>
        <a href="{{ route('orders.show', $order->id) }}">Order #{{ $order->id }} — {{ $order->created_at->format('Y-m-d') }}</a>
        <div class="muted">Total: {{ number_format($order->total,2) }} — Status: {{ $order->shipments->first()->status ?? 'pending' }}</div>
      </li>
    @endforeach
  </ul>
  {{ $orders->links() }}
@else
  <p class="muted">You have not placed any orders yet.</p>
@endif

@endsection
