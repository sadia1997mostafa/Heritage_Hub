@extends('layouts.app')
@section('title','Home — Heritage Hub')

@section('content')

  {{-- HERO --}}
  <section class="hero">
    <div class="hh-container hero-inner">
      <h1 class="reveal">Walk Through Time</h1>
      <p class="reveal delay-1">Explore heritage cities, crafts, and living culture.</p>
      <div class="hero-cta reveal delay-2">
        <a class="btn-primary" href="{{ route('shop') }}">See All Shops</a>
        <a class="btn-ghost" href="{{ route('skills') }}">Learn a Skill</a>
      </div>
    </div>
  </section>
  {{-- MAP just under navbar --}}
  @include('components.craft-map')
  {{-- ABOUT --}}
  <section id="about" class="pad-section hh-container">
    <h2 class="section-title">About</h2>
    <p class="section-text">
      Heritage Hub connects travelers, locals, and artisans. Discover stories, trails,
      and crafts that keep culture alive—curated across cities, rivers, and old towns.
    </p>
  </section>

  {{-- EXPLORE (cities preview) --}}
  <section id="explore" class="pad-section hh-container">
    <h2 class="section-title">Explore Cities</h2>
    <p class="section-text">Pick a city and start your journey.</p>
    <div class="city-grid">
      <a class="city-card" href="{{ route('shop') }}?city=Dhaka">Dhaka</a>
      <a class="city-card" href="{{ route('shop') }}?city=Chittagong">Chittagong</a>
      <a class="city-card" href="{{ route('shop') }}?city=Rajshahi">Rajshahi</a>
      <a class="city-card" href="{{ route('shop') }}?city=Khulna">Khulna</a>
      <a class="city-card" href="{{ route('shop') }}?city=Sylhet">Sylhet</a>
      <a class="city-card" href="{{ route('shop') }}?city=Barishal">Barishal</a>
    </div>
  </section>

  {{-- SEE ALL SHOPS (CTA block) --}}
  <section class="pad-section hh-container">
    <div class="see-all-shops">
      <div>
        <h2 class="section-title">See All Shops</h2>
        <p class="section-text">Browse verified artisans and cultural stores across Bangladesh.</p>
      </div>
      <a class="btn-primary" href="{{ route('shop') }}">Open Shop Directory</a>
    </div>
  </section>

  {{-- APP (just above footer) --}}
  <section id="app" class="pad-section hh-container app-section">
    <div class="app-card">
      <div>
        <h2 class="section-title">Get the App</h2>
        <p class="section-text">Save trails, offline guides, and get smart recommendations.</p>
        <div class="badges">
          <a href="#"><img src="https://developer.apple.com/assets/elements/badges/download-on-the-app-store.svg" alt="App Store"></a>
          <a href="#"><img src="https://upload.wikimedia.org/wikipedia/commons/c/cd/Get_it_on_Google_play.svg" alt="Google Play"></a>
        </div>
      </div>
      <div class="app-illustration" aria-hidden="true"></div>
    </div>
  </section>

@endsection
