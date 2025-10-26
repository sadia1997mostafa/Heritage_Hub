@extends('layouts.app')
@section('title','Search results — Heritage Hub')
@push('styles') @vite(['resources/css/shop.css']) @endpush

@section('content')
  <div class="hh-container pad-section">
    <h2 class="section-title">Search results{{ isset($q) && $q !== '' ? ' for "'.e($q).'"' : '' }}</h2>

    @if(!empty($searchProducts) && $searchProducts->count())
      <div class="prod-grid">
        @foreach($searchProducts as $p)
          @include('components.product-card', ['p' => $p])
        @endforeach
      </div>
      <div class="mt-4">{{ $searchProducts->links() }}</div>
    @elseif(isset($q) && $q !== '')
      <p>No products matched your search.</p>
    @endif

    @if(!empty($searchVendors) && $searchVendors->count())
      <h3 class="section-subtitle mt-6">Matching Shops</h3>
      <div class="vendor-grid">
        @foreach($searchVendors as $v)
          @include('components.vendor-card', ['v' => $v])
        @endforeach
      </div>
    @endif

    {{-- Matching cities/districts --}}
    @if(!empty($districtMatches) && $districtMatches->count())
      <h3 class="section-subtitle mt-6">Matching Cities</h3>
      <div class="grid" style="display:flex;gap:8px;flex-wrap:wrap;">
        @foreach($districtMatches as $d)
          <a class="chip" href="{{ route('district.show', $d->slug) }}">{{ $d->name }}</a>
        @endforeach
      </div>
    @endif

    <div class="mt-8">
      <a href="{{ route('shop') }}" class="btn-ghost">Back to shop</a>
    </div>
  </div>
@endsection
