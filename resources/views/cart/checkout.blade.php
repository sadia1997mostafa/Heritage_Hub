@extends('layouts.app')
@section('title','Checkout')
@section('content')
@vite(['resources/css/cart.css'])
<div class="hh-container pad-section">
  <div style="max-width:980px;margin:0 auto;">
    <h2 style="margin-bottom:10px">Checkout</h2>
    <p class="muted">Complete your order and choose a payment method. We currently support Cash on Delivery and Online payments (mock gateway for development).</p>

    <form method="POST" action="{{ route('checkout.submit') }}" class="checkout-form" id="checkout-form">
      @csrf

      <div style="display:grid;grid-template-columns:1fr 360px;gap:18px;align-items:start">
        <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:var(--shadow)">
          <h3 style="margin-top:0">Shipping details</h3>
          <div style="display:grid;gap:10px">
            <label>Name <input name="name" required class="input" /></label>
            <label>Phone <input name="phone" required class="input" /></label>
            <label>Address <textarea name="address" required class="input" rows="3"></textarea></label>
            <label>Notes <textarea name="notes" class="input" rows="2"></textarea></label>
          </div>

          <hr style="margin:16px 0">

          <h4>Payment method</h4>
          <div style="display:flex;gap:12px;align-items:center;margin-top:8px">
            <label style="display:flex;align-items:center;gap:8px">
              <input type="radio" name="payment_method" value="cod" checked />
              <span>Cash on Delivery</span>
            </label>
            <label style="display:flex;align-items:center;gap:8px">
              <input type="radio" name="payment_method" value="online" />
              <span>Pay Now (Online)</span>
            </label>
          </div>

          <div style="margin-top:12px">
            <button id="checkout-submit" class="btn primary" type="submit">Place Order (COD)</button>
            <button id="checkout-processing" class="btn primary" type="button" hidden disabled>Processing…</button>
          </div>
        </div>

        <aside class="summary" style="background:#fff;border-radius:12px;padding:18px;box-shadow:var(--shadow)">
          <h4 style="margin-top:0">Order summary</h4>
          <p class="muted">Items: {{ $summary['items'] ?? 0 }}</p>
          <p class="muted">Subtotal: ৳ {{ number_format($summary['subtotal'] ?? 0,2) }}</p>
          <p class="muted">Shipping: ৳ {{ number_format($summary['shipping'] ?? 0,2) }}</p>
          <hr>
          <h3>Total: ৳ {{ number_format($summary['total'] ?? 0,2) }}</h3>
          <p class="muted" style="font-size:.9rem;margin-top:8px">You will be redirected to a payment gateway if you choose Online payment.</p>
        </aside>
      </div>
    </form>
  </div>
</div>

<script>
  (function(){
    const form = document.getElementById('checkout-form');
    const submit = document.getElementById('checkout-submit');
    const processing = document.getElementById('checkout-processing');
    const radios = Array.from(document.querySelectorAll('input[name="payment_method"]'));

    function updateButton(){
      const val = document.querySelector('input[name="payment_method"]:checked').value;
      submit.textContent = val === 'online' ? 'Pay Now' : 'Place Order (COD)';
    }
    radios.forEach(r=>r.addEventListener('change', updateButton));
    updateButton();

    form.addEventListener('submit', function(e){
      // if online payment selected, submit via AJAX and follow redirect JSON
      const method = document.querySelector('input[name="payment_method"]:checked').value;
      if (method === 'online' && window.fetch) {
        e.preventDefault();
        submit.hidden = true; processing.hidden = false;

        const fd = new FormData(form);
        fetch(form.action, {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
          body: fd,
          credentials: 'same-origin'
        }).then(r => r.json()).then(json => {
          if (json && json.redirect) window.location.href = json.redirect;
          else {
            processing.hidden = true; submit.hidden = false;
            alert('Could not start payment. Please try again.');
          }
        }).catch(err => {
          processing.hidden = true; submit.hidden = false;
          alert('Network error, try again.');
        });
        return;
      }

      // fallback: normal POST (COD)
      submit.hidden = true; processing.hidden = false;
    });
  })();
</script>

<style>
  .input{ width:100%; padding:10px; border:1px solid #e6dccf; border-radius:8px }
  .summary h4{ margin-top:0 }
  .btn.primary{ padding:10px 16px }
</style>

@endsection
