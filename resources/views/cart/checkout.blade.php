@extends('layouts.app')
@section('title','Checkout')
@section('content')
@vite(['resources/css/cart.css'])
<div class="hh-container pad-section">
  <h2>Checkout — Cash on delivery</h2>
  <form method="POST" action="{{ route('checkout.submit') }}" class="checkout-form">
    @csrf
    <div style="display:grid;grid-template-columns:1fr 360px;gap:18px">
      <div>
        <label>Name <input name="name" required /></label>
        <label>Phone <input name="phone" required /></label>
        <label>Address <textarea name="address" required></textarea></label>
        <label>Notes <textarea name="notes"></textarea></label>
      </div>
      <aside class="summary">
        <h4>Order summary</h4>
        <p class="muted">Items: {{ $summary['items'] ?? 0 }}</p>
        <p class="muted">Subtotal: ৳ {{ number_format($summary['subtotal'] ?? 0,2) }}</p>
        <p class="muted">Shipping: ৳ {{ number_format($summary['shipping'] ?? 0,2) }}</p>
        <hr>
        <h3>Total: ৳ {{ number_format($summary['total'] ?? 0,2) }}</h3>
        <div style="margin-top:12px">
          <button class="btn-primary" type="submit">Place Order</button>
        </div>
      </aside>
    </div>
  </form>
</div>
@endsection
