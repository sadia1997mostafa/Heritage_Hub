@extends('layouts.vendor')
@section('title','Vendor Ledger')
@section('content')
<div class="hh-container pad-section">
  <h2>Vendor Ledger</h2>
  <p>This page shows vendor earnings, payouts and ledger entries.</p>
  <div class="card" style="display:flex;gap:18px">
    <div style="flex:1">
      <h4>Total Received</h4>
      <div style="font-size:22px">৳ {{ number_format($received ?? 0,2) }}</div>
      <div style="color:#777">(Total amount buyers paid for delivered/paid items)</div>
    </div>
    <div style="flex:1">
      <h4>Platform Fee (received)</h4>
      <div style="font-size:22px">৳ {{ number_format($receivedPlatformFee ?? 0,2) }}</div>
      <div style="color:#777">(Platform commission on received items)</div>
    </div>
    <div style="flex:1">
      <h4>Vendor Revenue (received)</h4>
      <div style="font-size:22px">৳ {{ number_format($vendorRevenue ?? 0,2) }}</div>
      <div style="color:#777">(Amount vendors keep after platform fee)</div>
    </div>
    <div style="flex:1">
      <h4>Pending</h4>
      <div style="font-size:22px">৳ {{ number_format($pending ?? 0,2) }}</div>
      <div style="color:#777">(Total pending amounts from buyers)</div>
    </div>
  </div>

  <div style="margin-top:20px" class="card">
    <h4>Received Entries</h4>
    @if(isset($receivedEntries) && $receivedEntries->count())
      <table style="width:100%;border-collapse:collapse;margin-top:8px">
        <tr><th>Date</th><th>Order</th><th>Gross</th><th>Platform fee</th><th>Vendor revenue</th><th>Status</th></tr>
        @foreach($receivedEntries as $e)
          <tr style="border-bottom:1px solid #eee">
            <td style="padding:8px">{{ $e->created_at->format('Y-m-d') }}</td>
            <td style="padding:8px">#{{ $e->order_id }}</td>
            <td style="padding:8px">৳ {{ number_format($e->gross_amount,2) }}</td>
            <td style="padding:8px">৳ {{ number_format($e->platform_fee,2) }}</td>
            <td style="padding:8px">৳ {{ number_format(($e->gross_amount - ($e->platform_fee ?? round($e->gross_amount * 0.10,2))),2) }}</td>
            <td style="padding:8px">{{ $e->status }}</td>
          </tr>
        @endforeach
      </table>
    @else
      <div style="color:#777">No received entries yet.</div>
    @endif
  </div>

  <div style="margin-top:20px" class="card">
    <h4>Pending Entries</h4>
    @if(isset($pendingEntries) && $pendingEntries->count())
      <table style="width:100%;border-collapse:collapse;margin-top:8px">
        <tr><th>Date</th><th>Order</th><th>Gross</th><th>Platform fee</th><th>Vendor revenue</th><th>Status</th></tr>
        @foreach($pendingEntries as $e)
          <tr style="border-bottom:1px solid #eee">
            <td style="padding:8px">{{ $e->created_at->format('Y-m-d') }}</td>
            <td style="padding:8px">#{{ $e->order_id }}</td>
            <td style="padding:8px">৳ {{ number_format($e->gross_amount,2) }}</td>
            <td style="padding:8px">৳ {{ number_format($e->platform_fee,2) }}</td>
            <td style="padding:8px">৳ {{ number_format(($e->gross_amount - ($e->platform_fee ?? round($e->gross_amount * 0.10,2))),2) }}</td>
            <td style="padding:8px">{{ $e->status }}</td>
          </tr>
        @endforeach
      </table>
    @else
      <div style="color:#777">No pending entries.</div>
    @endif
  </div>
</div>
@endsection
