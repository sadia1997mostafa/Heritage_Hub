@props(['p'])
<a href="{{ route('shop.product.show',$p->slug) }}" class="prod-card tilt-card">
  <div class="tilt-inner">
  <div class="prod-img">
  @if($p->first_image_path)
    <x-image :path="$p->first_image_path" :alt="$p->title" sizes="(max-width:480px) 160px, 320px" />
  @else
    <img src="{{ $p->first_image_url }}" alt="{{ $p->title }}" loading="lazy" decoding="async" class="prod-card-img">
  @endif
    @if($p->stock <= 0)
      <span class="badge oos">Out of stock</span>
    @endif
  </div>
  <div class="prod-body">
    <div class="prod-title">{{ $p->title }}</div>
    <div class="prod-meta">
      <span class="price">৳ {{ number_format($p->price,2) }}</span>
      <span class="cat">{{ $p->category->name ?? '' }}</span>
    </div>
  </div>
  </div>
</a>
