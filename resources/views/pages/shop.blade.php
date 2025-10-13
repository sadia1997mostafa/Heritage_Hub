@extends('layouts.app')
@section('title','Shop — Heritage Hub')

@section('content')
  <section class="parallax-hero">
    <div class="parallax-layer" style='background-image:url("{{ asset("images/heritage-map.jpg") }}")'></div>
    <div class="parallax-content">
      <div class="hh-container pad-section">
        <h2 class="section-title">Shop</h2>
        <p class="section-text">Discover artisan shops and crafted goods across Bangladesh.</p>
      </div>
    </div>
  </section>

  {{-- 3D rotating gallery (uses images you provide in public/images) --}}
  <section class="shop-rotator pad-section">
    <div class="hh-container">
      <div class="rotator-viewport">
        <div class="rotator" aria-hidden="false">
          <div class="rotor-slide"><img src="{{ asset('images/shop-rot-1.jpg') }}" alt="Crafts: textiles" loading="lazy"></div>
          <div class="rotor-slide"><img src="{{ asset('images/shop-rot-2.jpg') }}" alt="Crafts: pottery" loading="lazy"></div>
          <div class="rotor-slide"><img src="{{ asset('images/shop-rot-3.jpg') }}" alt="Weaving close-up" loading="lazy"></div>
          <div class="rotor-slide"><img src="{{ asset('images/shop-rot-4.jpg') }}" alt="Artisan at work" loading="lazy"></div>
        </div>
      </div>
      <div class="rotator-controls">
        <button class="rot-prev" aria-label="Previous">◀</button>
        <button class="rot-next" aria-label="Next">▶</button>
      </div>
    </div>
  </section>
  
  {{-- FEATURED CATEGORIES --}}
  @if(!empty($featuredCategories) && $featuredCategories->count())
  <section class="pad-section hh-container">
    <h2 class="section-title">Popular Categories</h2>
    <div class="chips">
      @foreach($featuredCategories as $cat)
        <a class="chip" href="{{ route('shop.category.show', $cat->slug) }}">{{ $cat->name }}</a>
      @endforeach
    </div>
  </section>
  @endif

  {{-- FEATURED PRODUCTS --}}
  @if(!empty($featuredProducts) && $featuredProducts->count())
  <section class="pad-section hh-container">
    <h2 class="section-title">Featured Crafts</h2>
    <div class="prod-grid">
      @foreach($featuredProducts as $p)
        @include('components.product-card', ['p' => $p])
      @endforeach
    </div>
    <div class="see-more">
      <a class="btn-ghost" href="{{ route('shop') }}">See more products</a>
    </div>
  </section>
  @endif

  {{-- FEATURED VENDORS --}}
  @if(!empty($featuredVendors) && $featuredVendors->count())
  <section class="pad-section hh-container">
    <h2 class="section-title">Featured Shops</h2>
    <div class="vendor-grid">
      @foreach($featuredVendors as $v)
        @include('components.vendor-card', ['v' => $v])
      @endforeach
    </div>
  </section>
  @endif
@endsection
