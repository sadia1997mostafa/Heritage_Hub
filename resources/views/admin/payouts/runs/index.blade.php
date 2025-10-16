@extends('layouts.admin')

@section('content')
<style>
.run-card { background: #fff; border-radius: 12px; padding: 18px; box-shadow: 0 12px 30px rgba(42,28,20,.06); }
.run-cta { display:flex; gap:10px; align-items:center; }
.btn-primary { background:#6B4E3D; color:white; padding:8px 12px; border-radius:8px; border:none; cursor:pointer }
.btn-muted { background:#f5f0ea; color:#5a3e2b; padding:8px 12px; border-radius:8px; border:none }
.vendor-row { display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f0ebe4 }
.vendor-left { display:flex; gap:12px; align-items:center }
.vendor-meta { color:#6b4e3d }
</style>

<h1>Payout Runs — Generate</h1>

<div class="run-card">
  <div class="run-cta">
    <form method="POST" action="{{ route('admin.payouts.runs.generate') }}">@csrf
      <button class="btn-primary">Generate payout run</button>
    </form>
    <div style="color:#7b6a5b">This will collect all pending vendor earnings and prepare a CSV for manual payout.</div>
  </div>

  <hr style="margin:12px 0" />

  @if($vendors->count())
    @foreach($vendors as $vendor)
      <div class="vendor-row">
        <div class="vendor-left">
          <div style="width:44px;height:44px;border-radius:8px;background:#f5efe6;display:flex;align-items:center;justify-content:center;font-weight:700;color:#6b4e3d">{{ strtoupper(substr($vendor->shop_name ?? ($vendor->user->name ?? 'V'),0,1)) }}</div>
          <div>
            <div class="vendor-meta">{{ $vendor->shop_name ?? ($vendor->user->name ?? 'Vendor') }}</div>
            <div style="color:#7b6a5b;font-size:0.9rem">{{ $vendor->user->email ?? '' }}</div>
          </div>
        </div>
        <div style="text-align:right">
          <div style="font-weight:700;color:#2a1c14">৳ {{ number_format($balances[$vendor->id] ?? 0,2) }}</div>
          <div style="color:#7b6a5b;font-size:0.85rem">{{ $vendor->user->payoutAccount->method ?? '—' }}</div>
        </div>
      </div>
    @endforeach
  @else
    <div style="color:#7b6a5b">No pending vendor earnings.</div>
  @endif
</div>

@endsection
