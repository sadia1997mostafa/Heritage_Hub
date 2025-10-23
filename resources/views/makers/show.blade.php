@extends('layouts.app')
@section('title', ($profile->shop_name ?? 'Maker') . ' — Maker')

@section('content')
<main class="hh-wrap">
  {{-- 3D animated loader overlay (shows until main images are ready) --}}
  <div id="maker-loader" aria-hidden="true">
    <div class="loader-center">
      <div class="cube">
        <div class="face f1"></div>
        <div class="face f2"></div>
        <div class="face f3"></div>
      </div>
      <div class="loader-text">Curating maker's story…</div>
    </div>
  </div>

  <section style="display:flex;gap:18px;align-items:center;margin-bottom:18px">
    <div id="maker-hero" style="flex:1;min-height:160px;background-image:url('{{ $profile->banner_url }}');background-size:cover;border-radius:12px;transition:transform .12s ease;will-change:transform"></div>
    <div style="width:320px">
      <img src="{{ $profile->logo_url }}" alt="{{ $profile->shop_name }}" style="width:100%;border-radius:10px;border:1px solid #eee">
      <h2 style="margin-top:8px">{{ $profile->shop_name }}
        @if($profile->approved_at)
          <span style="display:inline-block;margin-left:8px;background:#e6f6ec;color:#0a6b2b;padding:4px 8px;border-radius:999px;font-weight:600;font-size:12px">✔ Verified</span>
        @endif
      </h2>
      @auth('admin')
        <form method="POST" action="{{ route('admin.makers.verify', $profile->id) }}" style="margin-top:6px">
          @csrf
          <button class="btn" type="submit">@if($profile->approved_at) Unverify @else Verify @endif</button>
        </form>
      @endauth
      @if($profile->district)
        <div style="color:#666;margin-top:6px">{{ $profile->district->name }}</div>
      @endif
    </div>
  </section>

  @if(count($profile->gallery ?? []) > 0)
    <section style="margin-bottom:18px">
      <h3>Gallery</h3>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        @foreach($profile->gallery as $g)
          <a href="{{ $g }}" target="_blank" style="display:block;width:160px;height:120px;overflow:hidden;border-radius:8px;border:1px solid #eee">
            <img src="{{ $g }}" style="width:100%;height:100%;object-fit:cover;display:block" alt="gallery">
          </a>
        @endforeach
      </div>
    </section>
  @endif

  <section style="display:grid;grid-template-columns:2fr 1fr;gap:20px">
    <div>
      <h3>About</h3>
      <div style="background:#fff;padding:12px;border-radius:10px;border:1px solid #efe6d2">{!! nl2br(e($profile->heritage_story ?? $profile->description ?? 'No description yet.')) !!}</div>

      <h3 style="margin-top:12px">Products</h3>
      @if($products->isEmpty())
        <p class="hh-empty">This maker has not listed products yet.</p>
      @else
        <div class="hh-grid">
          @foreach($products as $p)
            <x-product-card :p="$p" />
          @endforeach
        </div>
        <div style="margin-top:12px">{{ $products->links() }}</div>
      @endif
    </div>

    <aside>
      <h4>Contact & Info</h4>
      <div style="background:#fff;padding:12px;border-radius:10px;border:1px solid #efe6d2">
        @if($profile->support_email)<div><strong>Email:</strong> {{ $profile->support_email }}</div>@endif
        @if($profile->phone)<div><strong>Phone:</strong> {{ $profile->phone }}</div>@endif
        @if($profile->vendor_category)<div style="margin-top:8px"><strong>Category:</strong> {{ $profile->vendor_category }}</div>@endif
      </div>
    </aside>
  </section>
</main>
@endsection

@push('styles')
<style>
/* Loader overlay */
#maker-loader{position:fixed;inset:0;background:linear-gradient(180deg,rgba(6,10,20,0.85),rgba(6,10,20,0.95));display:flex;align-items:center;justify-content:center;z-index:1200;backdrop-filter: blur(6px);transition:opacity .45s ease, visibility .45s ease}
#maker-loader.hide{opacity:0;visibility:hidden}
.loader-center{text-align:center;color:#fff}
.loader-text{margin-top:12px;color:#dfe8ff;letter-spacing:.5px}
.cube{width:74px;height:74px;position:relative;transform-style:preserve-3d;animation:spin 1.35s linear infinite;filter:drop-shadow(0 16px 30px rgba(0,0,0,.45))}
.cube .face{position:absolute;inset:0;border-radius:10px;background:linear-gradient(135deg,#ffd9a8,#ffb57c);opacity:.98}
.cube .f1{transform:translateZ(30px)}
.cube .f2{transform:rotateY(90deg) translateZ(30px);background:linear-gradient(135deg,#cddcff,#9fb7ff)}
.cube .f3{transform:rotateX(90deg) translateZ(30px);background:linear-gradient(135deg,#cfe8d4,#9fe3af)}
@keyframes spin{0%{transform:rotateX(10deg) rotateY(0deg)}50%{transform:rotateX(20deg) rotateY(180deg)}100%{transform:rotateX(10deg) rotateY(360deg)}}

/* Subtle hero tilt */
#maker-hero{transform-origin:center center}

/* Minor page polish */
.hh-wrap{transition:opacity .45s ease}

/* Responsive tweaks for maker page */
@media (max-width:900px){
  .hh-wrap{padding:12px}
  #maker-hero{min-height:140px}
  .hh-grid{grid-template-columns:repeat(auto-fill,minmax(200px,1fr))}
}
</style>
@endpush

@push('scripts')
<script>
(() => {
  const loader = document.getElementById('maker-loader');
  const hero = document.getElementById('maker-hero');

  // Wait for important images to load (hero + product thumbnails)
  const images = Array.from(document.images);
  const important = images.filter(img=>img.closest('.hh-card-img') || img.closest('#maker-hero'));

  let minShow = 600; // keep loader visible for at least this ms
  const start = Date.now();

  function hideLoader(){
    const elapsed = Date.now() - start;
    const wait = Math.max(0, minShow - elapsed);
    setTimeout(()=>{
      loader.classList.add('hide');
    }, wait);
  }

  if (important.length === 0) {
    // no images to wait for: hide after small delay
    setTimeout(hideLoader, 350);
  } else {
    let remaining = important.length;
    important.forEach(img => {
      if (img.complete) { remaining--; return; }
      img.addEventListener('load', ()=>{ remaining--; if (remaining<=0) hideLoader(); });
      img.addEventListener('error', ()=>{ remaining--; if (remaining<=0) hideLoader(); });
    });
    if (remaining<=0) hideLoader();
  }

  // Simple interactive tilt on hero (mouse move)
  if (hero) {
    hero.addEventListener('mousemove', e=>{
      const r = hero.getBoundingClientRect();
      const px = (e.clientX - r.left) / r.width - 0.5;
      const py = (e.clientY - r.top) / r.height - 0.5;
      const rx = (py * 6).toFixed(2);
      const ry = (px * -8).toFixed(2);
      hero.style.transform = `perspective(900px) rotateX(${rx}deg) rotateY(${ry}deg) scale(1.01)`;
    });
    hero.addEventListener('mouseleave', ()=>{ hero.style.transform = 'none'; });
  }
})();
</script>
@endpush
