@props(['p'])
<a href="{{ route('shop.product.show',$p->slug) }}" class="prod-card">
  <div class="prod-img">
    <img src="{{ $p->first_image_url }}" alt="{{ $p->title }}">
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
</a>
