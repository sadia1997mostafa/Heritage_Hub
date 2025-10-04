@extends('layouts.admin')
@section('title','Admin Dashboard')

@section('content')
@php
  // If your controller didn’t pass counts yet, compute safe fallbacks.
  $pendingProducts   = $pendingProducts   ?? \App\Models\Product::where('status','submitted')->count();
  $pendingVendors    = $pendingVendors    ?? \App\Models\VendorProfile::where('status','pending')->count();
  $pendingPayouts    = $pendingPayouts    ?? \App\Models\VendorPayoutAccount::where('status','pending')->count();
  $adminUser         = auth('admin')->user();
@endphp

<div class="admin-topcard" style="margin-top:16px">
  <div>Welcome, <strong>{{ $adminUser?->name ?? 'Admin' }}</strong></div>
  <form method="POST" action="{{ route('auth.logout') }}">@csrf
    <button class="admin-logout">Logout</button>
  </form>
</div>

<h1 class="page-title">Admin Dashboard</h1>

{{-- Quick KPI tiles --}}
<div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px">
  <a href="{{ route('admin.products.index') }}" class="card" style="text-decoration:none;color:inherit">
    <div style="font-weight:700">Pending Products</div>
    <div style="font-size:2rem;font-weight:800;color:#5A3E2B">{{ $pendingProducts }}</div>
    <div style="color:#7B6A5B">Review & approve submitted items</div>
  </a>

  <a href="{{ route('admin.vendors.index') }}" class="card" style="text-decoration:none;color:inherit">
    <div style="font-weight:700">Vendor Applications</div>
    <div style="font-size:2rem;font-weight:800;color:#5A3E2B">{{ $pendingVendors }}</div>
    <div style="color:#7B6A5B">Approve or reject vendor stores</div>
  </a>

  <a href="{{ route('admin.payouts.index') }}" class="card" style="text-decoration:none;color:inherit">
    <div style="font-weight:700">Payout Verifications</div>
    <div style="font-size:2rem;font-weight:800;color:#5A3E2B">{{ $pendingPayouts }}</div>
    <div style="color:#7B6A5B">Verify bKash/Nagad/Bank accounts</div>
  </a>
</div>

{{-- Quick actions --}}
<div class="card" style="margin-top:18px">
  <h2 style="margin:0 0 10px">Quick Actions</h2>
  <div style="display:flex;gap:10px;flex-wrap:wrap">
    <a class="btn primary" href="{{ route('admin.vendors.index') }}">Manage Vendors</a>
    <a class="btn primary" href="{{ route('admin.products.index') }}">Review Products</a>
    <a class="btn primary" href="{{ route('admin.payouts.index') }}">Payout Approvals</a>
    <a class="btn ghost"   href="{{ route('home') }}">← Go to Site Home</a>
  </div>
</div>
@endsection
