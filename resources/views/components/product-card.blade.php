@props(['p'])
@php
  $avg = round(\App\Models\Review::where('product_id',$p->id)->where('status','approved')->avg('rating') ?? 0,1);
  $count = \App\Models\Review::where('product_id',$p->id)->where('status','approved')->count();
@endphp

<div class="hh-card w-full bg-white rounded-lg shadow-sm overflow-hidden">
  <a href="{{ route('shop.product.show',$p->slug) }}" class="block">
    <div class="hh-card-img img-wrap bg-gray-50">
      @if($p->first_image_path)
        <x-image :path="$p->first_image_path" :alt="$p->title" class="object-cover w-full h-full" sizes="(max-width:480px) 320px, 480px" />
      @else
        <img src="{{ $p->first_image_url }}" alt="{{ $p->title }}" loading="lazy" decoding="async" class="object-cover w-full h-full">
      @endif
    </div>
    <div class="hh-card-body px-3 py-3">
      <h3 class="text-sm font-semibold text-gray-800 leading-tight truncate">{{ $p->title }}</h3>
      <div class="text-xs text-gray-500 mt-1">{{ $p->category->name ?? '' }}</div>
      @if($p->vendor)
        <div class="text-xs mt-2"><a href="{{ route('makers.show', $p->vendor->slug) }}" class="text-indigo-600">By {{ $p->vendor->shop_name }}</a></div>
      @endif

      <div class="mt-3 flex items-center justify-between">
        <div class="flex items-center text-sm text-gray-600">
          <div class="text-xs text-gray-400">{{ $count }} review{{ $count==1 ? '' : 's' }}</div>
        </div>

        <div class="text-sm font-semibold text-gray-800">৳ {{ number_format($p->price,0) }}</div>
      </div>
    </div>
  </a>

  <div class="px-3 pb-3">
    <button type="button" data-product-id="{{ $p->id }}" class="w-full inline-flex items-center justify-center px-4 py-2 bg-pink-500 hover:bg-pink-600 text-white text-sm rounded-full">Add to cart</button>
  </div>
</div>
