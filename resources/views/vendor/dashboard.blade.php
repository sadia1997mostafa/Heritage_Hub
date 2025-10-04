@extends('layouts.vendor')

@section('title','Dashboard')

@section('content')
@php
    // Expecting $vendor = VendorProfile for the logged-in vendor (with ->district relation)
    // If the controller didn’t precompute product counts, do a safe fallback here.
    // NOTE: In M2 we use products.vendor_id = vendor_profiles.id
    $vendorProfileId = $vendor->id ?? 0;

    $counts = $counts ?? [
        'total'     => \App\Models\Product::where('vendor_id', $vendorProfileId)->count(),
        'draft'     => \App\Models\Product::where('vendor_id', $vendorProfileId)->where('status','draft')->count(),
        'submitted' => \App\Models\Product::where('vendor_id', $vendorProfileId)->where('status','submitted')->count(),
        'approved'  => \App\Models\Product::where('vendor_id', $vendorProfileId)->where('status','approved')->count(),
    ];

    // Recent 5 products (optional – hide if none)
    $recent = $recent ?? \App\Models\Product::with('category')
                ->where('vendor_id', $vendorProfileId)
                ->latest()->take(5)->get();
@endphp

<h1 class="page-title">Vendor Dashboard</h1>

{{-- Store card --}}
<div class="card">
  <div style="display:flex; gap:16px; align-items:center; flex-wrap:wrap;">
    @if($vendor->shop_logo_path)
      <img src="{{ asset('storage/'.$vendor->shop_logo_path) }}"
           alt="Shop Logo"
           style="height:64px; width:64px; object-fit:cover; border-radius:12px; box-shadow: var(--shadow);">
    @endif
    <div>
      <h2 style="margin:0">{{ $vendor->shop_name }}</h2>
      <div class="muted">{{ $vendor->description }}</div>
    </div>
    <div style="margin-left:auto">
      <span class="badge {{ $vendor->status }}">{{ ucfirst($vendor->status) }}</span>
    </div>
  </div>

  <div style="display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; margin-top:14px">
    <div><strong>Phone:</strong> {{ $vendor->phone }}</div>
    <div><strong>District:</strong> {{ $vendor->district->name ?? '—' }}</div>
    <div style="grid-column:1 / -1"><strong>Address:</strong> {{ $vendor->address }}</div>
    @if($vendor->rejection_reason && $vendor->status === 'rejected')
      <div class="muted" style="grid-column:1 / -1"><strong>Rejection reason:</strong> {{ $vendor->rejection_reason }}</div>
    @endif
  </div>

  <div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap">
    <a href="{{ route('vendor.store.setup') }}" class="btn ghost">Edit Store Profile</a>
    <a href="{{ route('vendor.payout.form') }}" class="btn ghost">Setup / Update Payout</a>
  </div>
</div>

{{-- Products snapshot --}}
<div class="card" style="margin-top:16px">
  <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
    <h2 style="margin:0">My Products</h2>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
      <a href="{{ route('vendor.products.index') }}" class="btn ghost">View All</a>
      <a href="{{ route('vendor.products.create') }}" class="btn primary">+ Add Product</a>
    </div>
  </div>

  <div style="display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; margin-top:12px">
    <div class="kpi-tile">
      <div class="kpi-label">Total</div>
      <div class="kpi-value">{{ $counts['total'] }}</div>
    </div>
    <div class="kpi-tile">
      <div class="kpi-label">Draft</div>
      <div class="kpi-value">{{ $counts['draft'] }}</div>
    </div>
    <div class="kpi-tile">
      <div class="kpi-label">Submitted</div>
      <div class="kpi-value">{{ $counts['submitted'] }}</div>
    </div>
    <div class="kpi-tile">
      <div class="kpi-label">Approved</div>
      <div class="kpi-value">{{ $counts['approved'] }}</div>
    </div>
  </div>

  {{-- Recent products table (optional) --}}
  @if($recent->count())
    <div style="margin-top:16px">
      <h3 style="margin:0 0 8px">Recent</h3>
      <table class="table">
        <tr>
          <th>Title</th>
          <th>Category</th>
          <th>Status</th>
          <th style="width:1%">Actions</th>
        </tr>
        @foreach($recent as $p)
          <tr>
            <td>{{ $p->title }}</td>
            <td>{{ $p->category->name ?? '—' }}</td>
            <td><span class="badge {{ $p->status }}">{{ ucfirst($p->status) }}</span></td>
            <td class="nowrap">
              @if($p->status === 'draft')
                <form method="POST" action="{{ route('vendor.products.submit',$p) }}">
                  @csrf
                  <button class="btn ghost" style="padding:6px 10px">Submit</button>
                </form>
              @else
                —
              @endif
            </td>
          </tr>
        @endforeach
      </table>
    </div>
  @endif
</div>

{{-- If vendor is not approved, small note --}}
@if($vendor->status !== 'approved')
  <div class="card" style="margin-top:12px; border-left:6px solid var(--accent)">
    Your store is <strong>{{ $vendor->status }}</strong>. Some features (adding products or payout) may be restricted until approval.
  </div>
@endif
@endsection
