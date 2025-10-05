@extends('layouts.app')
@section('title', $product->title)

@push('styles') @vite(['resources/css/shop.css']) @endpush

@section('content')
<div class="container">
  <div class="product-show">
    <div class="gallery">
      @forelse($product->media as $m)
        <img src="{{ asset('storage/'.$m->path) }}" alt="{{ $product->title }}">
      @empty
        <img src="{{ $product->first_image_url }}" alt="{{ $product->title }}">
      @endforelse
    </div>

    <div class="details card">
      <h1 class="prod-title-lg">{{ $product->title }}</h1>
      <div class="muted">
        <a href="{{ route('shop.category.show',$product->category->slug) }}">{{ $product->category->name }}</a>
        • Store: <a href="{{ route('shop.store.show',$product->vendor->slug) }}">{{ $product->vendor->shop_name }}</a>
        @if($product->vendor->district) • {{ $product->vendor->district->name }} @endif
      </div>

      <div class="price-lg">৳ {{ number_format($product->price,2) }}</div>
      <div class="stock">
        @if($product->stock > 0)
          <span class="badge ok">In Stock: {{ $product->stock }}</span>
        @else
          <span class="badge oos">Out of Stock</span>
        @endif
      </div>

      <p class="desc">{{ $product->description }}</p>

      {{-- M4 will add Add-to-Cart. For now just CTA placeholder --}}
      <a class="btn primary" href="{{ route('shop.store.show',$product->vendor->slug) }}">Visit Store</a>
    </div>
  </div>

  @if($related->count())
    <h3 class="mt">More from this category</h3>
    <div class="grid">
      @foreach($related as $p)
        <x-product-card :p="$p"/>
      @endforeach
    </div>
  @endif
</div>
@endsection
