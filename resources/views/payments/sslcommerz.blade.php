@extends('layouts.app')

@section('title','SSLCommerz Checkout')

@section('content')
  <div class="hh-container pad-section" style="max-width:680px;margin:30px auto;text-align:center">
    <h2>SSLCommerz Sandbox Checkout</h2>
    <p class="muted">This page simulates redirecting to SSLCommerz. In production replace with server-to-server init and signature checks.</p>

    <form method="POST" action="https://sandbox.sslcommerz.com/gwprocess/v4/api.php">
      {{-- required params for sslcommerz sandbox example --}}
      <input type="hidden" name="store_id" value="testbox" />
      <input type="hidden" name="store_passwd" value="qwerty" />
      <input type="hidden" name="total_amount" value="{{ number_format($intent->amount/100,2) }}" />
      <input type="hidden" name="currency" value="{{ $intent->currency }}" />
      <input type="hidden" name="tran_id" value="{{ $intent->external_id }}" />
      <input type="hidden" name="success_url" value="{{ route('payment.mock.return', ['id'=>$intent->id, 'action'=>'success']) }}" />
      <input type="hidden" name="fail_url" value="{{ route('payment.mock.return', ['id'=>$intent->id, 'action'=>'cancel']) }}" />
      <input type="hidden" name="cancel_url" value="{{ route('payment.mock.return', ['id'=>$intent->id, 'action'=>'cancel']) }}" />

      <div style="margin-top:18px">
        <button class="btn primary">Continue to SSLCommerz Sandbox</button>
      </div>
    </form>
  </div>
@endsection
