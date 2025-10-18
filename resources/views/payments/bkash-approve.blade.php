<!doctype html>
<html>
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>bKash Approval (Mock)</title>
    <style>body{font-family:Inter,system-ui,Arial,sans-serif;display:grid;place-items:center;height:100vh;background:linear-gradient(180deg,#fff,#fbf4e8)}.card{padding:20px;border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,.06);background:#fff;max-width:640px;text-align:center}.btn{padding:10px 14px;border-radius:8px;background:#6B4E3D;color:#fff;border:0;font-weight:700}.muted{color:#666;margin-top:8px}</style>
  </head>
  <body>
    <div class="card">
      <h3>Approve payment for intent #{{ $intent->id }}</h3>
      <p class="muted">Amount: {{ number_format($intent->amount/100,2) }} {{ $intent->currency }}</p>
      <form method="POST" action="{{ route('bkash.execute') }}">
        @csrf
        <input type="hidden" name="paymentID" value="mockpay-{{ $intent->id }}" />
        <input type="hidden" name="paymentToken" value="mock-token-{{ $intent->id }}" />
        <input type="hidden" name="intent" value="{{ $intent->id }}" />
        <button class="btn">Approve Payment</button>
      </form>
    </div>
  </body>
</html>
