@extends('layouts.app')
@section('title', $store->shop_name)

@push('styles') @vite(['resources/css/shop.css']) @endpush

@section('content')
<div class="container">
  <div class="store-hero card">
    <div class="store-left">
      <img class="logo" src="{{ $store->shop_logo_path ? asset('storage/'.$store->shop_logo_path) : asset('images/default-shop.png') }}" alt="{{ $store->shop_name }}">
      <div>
        <h1 class="store-title">{{ $store->shop_name }}</h1>
        <div class="muted">
          @if($store->district) {{ $store->district->name }} • @endif
          {{ $store->support_email ?? '—' }} • {{ $store->support_phone ?? '—' }}
        </div>
      </div>
    </div>
    @if($store->banner_path)
      <img class="banner" src="{{ asset('storage/'.$store->banner_path) }}" alt="Banner">
    @endif
  </div>

  @if($store->description)
    <div class="card">{{ $store->description }}</div>
  @endif
  @if($store->heritage_story)
    <div class="card"><strong>Heritage Story:</strong><br>{{ $store->heritage_story }}</div>
  @endif

  <h3 class="mt">Products</h3>
  <div class="grid">
    @foreach($products as $p)
      <x-product-card :p="$p"/>
    @endforeach
  </div>
  <div class="pagination">{{ $products->links() }}</div>
</div>
@endsection
