@props(['v'])
@php
  $hasSlug = !empty($v->slug);
  $img = $v->logo_url ?? $v->logoUrl ?? asset('images/default-shop.png');
  $title = $v->shop_name ?? $v->name;
  $districtName = $v->district->name ?? '';
@endphp

@if($hasSlug)
  <a href="{{ route('shop.store.show', $v->slug) }}" class="vendor-card tilt-card">
    <div class="tilt-inner">
      @php
        $src = $img;
        $candidateSizes = [320,640,1024];
        $entries = [];
        if (!empty($v->shop_logo_path)) {
            foreach ($candidateSizes as $w) {
                $candidate = preg_replace('/\.[a-zA-Z0-9]+$/', "+-{$w}.webp", $v->shop_logo_path);
                // our generator uses '-{w}.webp' naming
                $candidate = preg_replace('/\.[^.]+$/', "-{$w}.webp", $v->shop_logo_path);
                $full = public_path('storage/'.$candidate);
                if (file_exists($full)) {
                    $entries[$w] = asset('storage/'.$candidate);
                }
            }
            if (!empty($entries)) {
                // use original for fallback
                $src = asset('storage/'.$v->shop_logo_path);
            }
        }
      @endphp
      <img src="{{ $src }}"
           @if(!empty($entries))
             srcset="@foreach($entries as $w=>$u){{ $u }} {{ $w }}w,@endforeach"
             sizes="(max-width:480px) 160px, 320px"
           @endif
           alt="{{ $title }}" loading="lazy" decoding="async" class="vendor-logo">
      <div>
        <div class="vendor-name">{{ $title }}</div>
        <div class="vendor-meta">{{ $districtName }}</div>
      </div>
    </div>
  </a>
@else
  <div class="vendor-card vendor-card--no-slug" aria-disabled="true">
  <img src="{{ $img }}" alt="{{ $title }}" loading="lazy" decoding="async" class="vendor-logo">
    <div>
      <div class="vendor-name">{{ $title }}</div>
      <div class="vendor-meta">{{ $districtName }}</div>
    </div>
  </div>
@endif
