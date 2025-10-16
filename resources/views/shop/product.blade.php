@extends('layouts.app')
@section('title', $product->title)

@push('styles') @vite(['resources/css/shop.css']) @endpush

@section('content')
<div class="container">
  <div class="product-show">
    <div class="gallery">
      @php $medias = $product->media->all(); @endphp
      @if(count($medias))
        <div class="gallery-main">
          <x-image :path="$medias[0]->path" :alt="$product->title" sizes="(max-width:900px) 90vw, 900px" />
        </div>
        <div class="gallery-thumbs">
          @foreach($medias as $m)
            <button class="thumb" type="button" data-src="{{ asset('storage/'.$m->path) }}">
              <x-image :path="$m->path" :alt="$product->title" sizes="200px" />
            </button>
          @endforeach
        </div>
      @else
        <div class="gallery-main">
          <img src="{{ $product->first_image_url }}" alt="{{ $product->title }}" loading="eager" decoding="async" class="prod-photo" width="900" height="600" />
        </div>
      @endif
    </div>

    <div class="details card">
      <h1 class="prod-title-lg">{{ $product->title }}</h1>
      <div class="muted">
        <a href="{{ route('shop.category.show',$product->category->slug) }}">{{ $product->category->name }}</a>
        • Store: 
        @if(!empty($product->vendor->slug))
          <a href="{{ route('shop.store.show',$product->vendor->slug) }}">{{ $product->vendor->shop_name }}</a>
        @else
          <span>{{ $product->vendor->shop_name }}</span>
        @endif
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

      {{-- Add to cart form (M4) --}}
      @if($product->stock > 0)
      <form method="POST" action="{{ route('cart.add') }}">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}" />
        <label>Qty <input type="number" name="qty" value="1" min="1" max="{{ $product->stock }}" /></label>
        <button class="btn primary" type="submit">Add to cart</button>
      </form>
      @else
      <a class="btn primary disabled" href="#">Out of stock</a>
      @endif
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

  {{-- Reviews --}}
  @include('product.partials.reviews')
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', ()=>{
  const main = document.querySelector('.gallery-main img');
  if (!main) return;
  document.querySelectorAll('.gallery-thumbs .thumb').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const src = btn.dataset.src;
      main.src = src;
    });
  });
});
</script>
@endpush
