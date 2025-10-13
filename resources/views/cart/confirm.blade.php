@extends('layouts.app')
@section('title','Order Confirmation')
@section('content')
<div class="hh-container pad-section">
  <h2>Order #{{ $order->id }} Received</h2>
  <p>Thank you — your order has been placed. Total: {{ number_format($order->total,2) }}</p>
</div>
@endsection
