@extends('layouts.app')
@section('title','Cart')
@section('content')
@vite(['resources/css/cart.css'])
<div class="hh-container pad-section cart-page">
  <h2>Your Cart</h2>
  @if(empty($cart))
    <p class="muted">Your cart is empty.</p>
  @else
    @php
      $summary = ['items' => 0, 'subtotal' => 0];
      foreach($cart as $vendorId => $lines) {
        foreach($lines as $productId => $line) {
          $summary['items'] += $line['qty'] ?? 0;
          $summary['subtotal'] += ($line['price'] ?? 0) * ($line['qty'] ?? 1);
        }
      }
      $summary['shipping'] = 0; // placeholder: per-vendor shipping can be applied later
      $summary['total'] = $summary['subtotal'] + $summary['shipping'];
    @endphp

    <div class="cart-grid">
      <main class="cart-list">
        @foreach($cart as $vendorId => $lines)
          @php($vendor = $vendors[$vendorId] ?? null)
          <section class="vendor-block">
            <div class="vendor-head">
              @if($vendor?->shop_logo_path)
                <x-image :path="$vendor->shop_logo_path" :alt="$vendor->shop_name" sizes="64px" />
              @else
                <img class="logo" src="{{ asset('images/default-shop.png') }}" alt="{{ $vendor?->shop_name ?? 'Vendor' }}">
              @endif
              <div>
                <h3>{{ $vendor?->shop_name ?? ('Vendor #'.$vendorId) }}</h3>
                <div class="muted">{{ $vendor?->district?->name ?? '' }}</div>
              </div>
            </div>

            <table class="cart-table">
              <thead><tr><th>Product</th><th class="muted">Qty</th><th class="muted">Price</th><th></th></tr></thead>
              <tbody>
                @foreach($lines as $productId => $line)
                  @php($p = $products[$productId] ?? null)
                  <tr>
                    <td>
                      <div class="cart-item-media">
                        @if($p?->first_image_path)
                          <x-image :path="$p->first_image_path" :alt="$p->title" sizes="78px" />
                        @else
                          <img src="{{ $p?->first_image_url ?? asset('images/default-product.png') }}" alt="{{ $p?->title ?? '' }}" class="prod-thumb" loading="lazy" decoding="async" width="78" height="78">
                        @endif
                        <div>
                          <div class="cart-item-title">{{ $p?->title ?? ('Product #'.$productId) }}</div>
                          <div class="muted">{{ $p?->category?->name ?? '' }}</div>
                        </div>
                      </div>
                    </td>
                    <td>
                      <form method="POST" action="{{ route('cart.update') }}">@csrf
                        <input type="hidden" name="vendor_id" value="{{ $vendorId }}" />
                        <input type="hidden" name="product_id" value="{{ $productId }}" />
                        <input name="qty" type="number" value="{{ $line['qty'] }}" min="0" class="qty-input" />
                        <button type="submit" class="btn-link small">Update</button>
                      </form>
                    </td>
                    <td>৳ {{ number_format($line['price'],2) }}</td>
                    <td>
                      <form method="POST" action="{{ route('cart.remove.vendor', $vendorId) }}">@csrf
                        <button type="submit" class="btn-link small">Remove vendor</button>
                      </form>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </section>
        @endforeach
      </main>

      <aside class="summary summary-sticky">
        <h4>Order summary</h4>
        <p class="muted">Items: {{ $summary['items'] }}</p>
        <p class="muted">Subtotal: ৳ {{ number_format($summary['subtotal'],2) }}</p>
        <p class="muted">Shipping: ৳ {{ number_format($summary['shipping'],2) }}</p>
        <hr>
        <h3>Total: ৳ {{ number_format($summary['total'],2) }}</h3>
        <div style="margin-top:12px">
          @if(auth()->check())
            <a class="btn-primary btn-block" href="{{ route('checkout.form') }}">Proceed to checkout</a>
          @else
            <button id="checkout-guest" class="btn-primary btn-block">Proceed to checkout</button>
          @endif
        </div>
      </aside>
    </div>
  @endif
</div>
@if(! auth()->check())
  <!-- Login required modal -->
  <div id="loginModal" class="modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);align-items:center;justify-content:center;z-index:1000;">
    <div style="background:#fff;padding:20px;border-radius:8px;max-width:420px;width:100%;box-shadow:0 6px 18px rgba(0,0,0,0.12)">
      <h3>You must be logged in</h3>
      <p>To place an order you need to be signed in. Would you like to login now?</p>
  <div style="display:flex;gap:8px;margin-top:12px">
  <a id="loginNow" class="btn-primary" href="{{ route('login', ['redirect' => route('checkout.form')]) }}">Login</a>
        <button id="loginCancel" class="btn-ghost">Cancel</button>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function(){
      var btn = document.getElementById('checkout-guest');
      var modal = document.getElementById('loginModal');
      var cancel = document.getElementById('loginCancel');
      if (!btn) return;
      btn.addEventListener('click', function(e){
        e.preventDefault();
        modal.style.display = 'flex';
      });
      cancel.addEventListener('click', function(){ modal.style.display = 'none'; });
    });
  </script>
@endif
@endsection
