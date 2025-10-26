<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>@yield('title','Heritage Hub')</title>

  {{-- Vite --}}
  @php $viteManifest = public_path('build/manifest.json'); @endphp
  @if (file_exists($viteManifest))
    @vite(['resources/css/app.css','resources/js/app.js'])
    {{-- Fallback: also inject the built app.css directly from the manifest in case @vite doesn't output tags in some envs --}}
    @php
      try {
        $__hh_manifest = json_decode(file_get_contents($viteManifest), true);
        if (isset($__hh_manifest['resources/css/app.css']['file'])) {
          $__hh_css = 'build/' . $__hh_manifest['resources/css/app.css']['file'];
        } else {
          $__hh_css = null;
        }
      } catch (\Throwable $__hh_e) { $__hh_css = null; }
    @endphp
    @if(!empty($__hh_css))
      <link rel="stylesheet" href="{{ asset($__hh_css) }}">
    @endif
    {{-- Note: do not hardcode specific hashed filenames here to avoid 404s during development; rely on the manifest or @vite tags. --}}
  @else
    {{-- Vite manifest missing; skipping asset injection (dev server or build not running) --}}
  @endif

  {{-- Page-specific styles (allows views to push theme CSS) --}}
  @stack('styles')

  {{-- Lightweight responsive helpers (mobile-first tweaks) --}}
  <style>
    /* container fallback and consistent padding */
    .hh-container, .container { max-width:1100px; margin:0 auto; padding:0 16px; box-sizing:border-box; }

    /* Mobile / tablet adjustments */
    @media (max-width: 900px) {
      .hh-header-row { display:flex; flex-wrap:wrap; align-items:center; gap:8px; }
      .hh-search { order:3; flex:1 1 100%; margin:8px 0; }
      .hh-actions { order:2; display:flex; gap:8px; align-items:center; }
      #hh-nav { display:none !important; }
      #hh-burger { display:inline-flex; }
      .hh-menu { flex-direction:column; gap:8px; }
      .hh-toplinks { display:flex; flex-wrap:wrap; gap:8px; }
      .mega { position:static; max-height:none; overflow:visible; }
      .hh-main { padding:12px; }
      img, video, .responsive { max-width:100%; height:auto; }
      table { width:100%; }
      .table-responsive { overflow-x:auto; -webkit-overflow-scrolling:touch; }
      .hh-logo .hh-wordmark span { font-size:1.1rem; }
      .hh-search input { width:100%; }
      .hh-actions select { max-width:120px; }
    }

    @media (max-width: 480px) {
      .hh-toplinks { font-size:14px; }
      .hh-logo .hh-wordmark span { font-size:1rem; }
      .hh-badge { display:none; }
      .hh-actions select { display:none; }
      .hh-search button { padding:8px; }
    }
  </style>

  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- Fonts: Latin + Bengali --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Inter:wght@400;600;800&family=Hind+Siliguri:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="hh-body">

  {{-- thin scroll progress (ornamental) --}}
  <div id="hh-progress"></div>

  {{-- ornamental top strip (subtle pattern) --}}
  <div class="hh-motif">
    <svg viewBox="0 0 1200 30" preserveAspectRatio="none" aria-hidden="true">
      <defs>
        <pattern id="tile" width="60" height="30" patternUnits="userSpaceOnUse">
          <path d="M0 15 Q15 0 30 15 T60 15" fill="none" stroke="rgba(217,164,65,.35)" stroke-width="1.2"/>
          <path d="M0 15 Q15 30 30 15 T60 15" fill="none" stroke="rgba(217,164,65,.18)" stroke-width="1.2"/>
        </pattern>
      </defs>
      <rect width="1200" height="30" fill="url(#tile)" />
    </svg>
  </div>

  {{-- Top utility bar --}}
  <div class="hh-topbar">
    <div class="hh-container">
      <ul class="hh-toplinks">
  <li><a href="#" data-auth-open="register" data-auth-vendor="1">Become a Seller</a></li>
        <li><a href="{{ route('skills') }}">Workshops</a></li>
        <li class="hh-divider"></li>

       
        </li>

        @auth
  <li style="display:flex;align-items:center;gap:8px">
    @include('partials.notifications')
    <a href="{{ route('home') }}">Hi, {{ auth()->user()->name }}</a>
    <a style="margin-left:12px" href="{{ route('orders.index') }}">My Orders</a>
  </li>
  <li>
    <form action="{{ route('auth.logout') }}" method="POST">@csrf
      <button type="submit" class="linklike">Logout</button>
    </form>
  </li>
@else
  <li><a href="#" data-auth-open="login">Login</a></li>
  <li><a class="accent" href="#" data-auth-open="register">Sign Up</a></li>
@endauth

      </ul>
    </div>
  </div>

  {{-- Header with logo + search + cart --}}
  <header id="hh-header" class="hh-header">
    <div class="hh-container hh-header-row">
      <a href="{{ route('home') }}" class="hh-logo" aria-label="Heritage Hub">
        {{-- wordmark with small filigree --}}
        <div class="hh-wordmark">
          <span>HeritageHub</span>
          <svg viewBox="0 0 80 10" aria-hidden="true"><path d="M2 5h76" stroke="rgba(217,164,65,.55)" stroke-width="1.5" stroke-linecap="round"/><circle cx="40" cy="5" r="2.5" fill="rgba(217,164,65,.45)"/></svg>
        </div>
      </a>

      <form action="{{ route('search') }}" class="hh-search" role="search">
        <input name="q" type="search" placeholder="Search crafts, cities, festivals…" />
        <button aria-label="Search">
          <svg viewBox="0 0 24 24"><path d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15z" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/></svg>
        </button>
      </form>

     

      <div class="hh-actions">
        <a class="hh-cart" href="{{ route('cart') }}" aria-label="Cart">
          <svg viewBox="0 0 24 24"><path d="M6 6h15l-1.5 9h-12zM6 6l-2-2H2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="9" cy="20" r="1.6"/><circle cx="18" cy="20" r="1.6"/></svg>
          <span class="hh-badge" id="hh-cart-count">0</span>
        </a>
        <select id="hh-theme-select" aria-label="Theme" style="margin-right:8px">
          <option value="default">Heritage</option>
          <option value="saffron">Saffron</option>
          <option value="teal">Teal</option>
          <option value="forest">Forest</option>
        </select>
        <button id="hh-burger" class="hh-burger" aria-label="Menu">
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>

    {{-- Primary nav with anchors --}}
    @php
      $onHome = request()->routeIs('home');
      $a = fn($id) => $onHome ? "#$id" : route('home')."#$id";
    @endphp
    <nav id="hh-nav" class="hh-nav">
      <div class="hh-container">
        <ul class="hh-menu">
          <li><a href="{{ route('home') }}" class="with-orn">Home</a></li>
          <li><a href="{{ $a('about') }}" class="with-orn">About</a></li>
          <li class="has-mega">
            <a href="{{ route('shop') }}" class="with-orn" aria-haspopup="true" aria-expanded="false">Shop</a>
            <div class="mega" role="menu" aria-label="Shop categories">
              <div class="mega-row">
                <a href="{{ route('shop.category.show', 'jamdani') }}">Jamdani</a>
                <a href="{{ route('shop.category.show', 'muslin') }}">Muslin</a>
                <a href="{{ route('shop.category.show', 'nakshi-kantha') }}">Nakshi Kantha</a>
                <a href="{{ route('shop.category.show', 'tangail-saree') }}">Tangail Saree</a>
                <a href="{{ route('shop') }}?cat=Muslin">Muslin</a>
                <a href="{{ route('shop') }}?cat=Nakshi Kantha">Nakshi Kantha</a>
                <a href="{{ route('shop') }}?cat=Tangail Saree">Tangail Saree</a>
                <a href="{{ route('shop.category.show', 'woodcarving') }}">Woodcarving</a>
                <a href="{{ route('shop.category.show', 'shital-pati') }}">Shital Pati</a>
                <a href="{{ route('shop.category.show', 'rickshaw-art') }}">Rickshaw Art</a>
                <a href="{{ route('shop.category.show', 'bamboo-cane') }}">Bamboo & Cane</a>
                <a href="{{ route('shop') }}?cat=Shital Pati">Shital Pati</a>
                <a href="{{ route('shop') }}?cat=Rickshaw Art">Rickshaw Art</a>
                <a href="{{ route('shop') }}?cat=Bamboo & Cane">Bamboo & Cane</a>
                <a href="{{ route('shop.category.show', 'dokra-metal') }}">Dokra Metal</a>
                <a href="{{ route('shop.category.show', 'brass-copper') }}">Brass & Copper</a>
                <a href="{{ route('shop.category.show', 'terracotta') }}">Terracotta</a>
                <a href="{{ route('shop.category.show', 'pottery') }}">Pottery</a>
                <a href="{{ route('shop') }}?cat=Brass & Copper">Brass & Copper</a>
                <a href="{{ route('shop') }}?cat=Terracotta">Terracotta</a>
                <a href="{{ route('shop') }}?cat=Pottery">Pottery</a>
                <a href="{{ route('shop.category.show', 'folk-masks') }}">Folk Masks</a>
                <a href="{{ route('shop.category.show', 'nakshi-dolls') }}">Nakshi Dolls</a>
                <a href="{{ route('shop.category.show', 'scroll-art') }}">Scroll Art</a>
                <a href="{{ route('shop.category.show', 'festival-decor') }}">Festival Decor</a>
                <a href="{{ route('shop') }}?cat=Nakshi Dolls">Nakshi Dolls</a>
                <a href="{{ route('shop') }}?cat=Scroll Art">Scroll Art</a>
                <a href="{{ route('shop') }}?cat=Festival Decor">Festival Decor</a>
              </div>
            </div>
          </li>

          <li><a href="{{ $a('app') }}" class="with-orn">App</a></li>
          <li><a href="{{ route('vlogs.index') }}" class="with-orn">Heritage Timeline</a></li>
          <li><a href="{{ route('events.index') }}" class="with-orn">Events</a></li>
          <li><a href="{{ $a('contact') }}" class="with-orn">Contact</a></li>
        </ul>
      </div>
    </nav>
    @if($onHome)
  {{-- gold lace under header (home only) --}}
  <div class="hh-lace" aria-hidden="true">
    <svg viewBox="0 0 1200 36" preserveAspectRatio="none">
      <defs>
        <linearGradient id="goldfade" x1="0" x2="1" y1="0" y2="0">
          <stop offset="0" stop-color="rgba(217,164,65,.0)"/>
          <stop offset="0.1" stop-color="rgba(217,164,65,.30)"/>
          <stop offset="0.9" stop-color="rgba(217,164,65,.30)"/>
          <stop offset="1" stop-color="rgba(217,164,65,.0)"/>
        </linearGradient>
      </defs>
      <!-- scalloped lace -->
      <path d="M0 18
               Q 40 0   80 18
               T 160 18
               T 240 18
               T 320 18
               T 400 18
               T 480 18
               T 560 18
               T 640 18
               T 720 18
               T 800 18
               T 880 18
               T 960 18
               T 1040 18
               T 1120 18
               T 1200 18" fill="none" stroke="url(#goldfade)" stroke-width="2"/>
      <path d="M0 22
               Q 40 36 80 22
               T 160 22 T 240 22 T 320 22 T 400 22 T 480 22 T 560 22 T 640 22
               T 720 22 T 800 22 T 880 22 T 960 22 T 1040 22 T 1120 22 T 1200 22"
            fill="none" stroke="rgba(217,164,65,.18)" stroke-width="1"/>
    </svg>
  </div>
@endif

  </header>

  <main class="hh-main">@yield('content')</main>

  {{-- Toast container for small notifications --}}
  <div id="hh-toast" style="position:fixed;top:18px;right:18px;z-index:12000"></div>

  {{-- filigree divider before footer --}}
  <div class="hh-filigree" aria-hidden="true">
    <svg viewBox="0 0 1200 50" preserveAspectRatio="none">
      <path d="M0 25 Q300 0 600 25 T1200 25" fill="none" stroke="rgba(217,164,65,.35)" stroke-width="1.5"/>
      <path d="M0 30 Q300 55 600 30 T1200 30" fill="none" stroke="rgba(217,164,65,.25)" stroke-width="1.5"/>
    </svg>
  </div>

  {{-- Footer = CONTACT target --}}
  <footer id="contact" class="hh-footer">
    <div class="hh-container footer-grid">
      <div>
        <h4>About Heritage Hub</h4>
        <p>Discover heritage sites, crafts, and festivals across Bangladesh.</p>
      </div>
      <div>
        <h4>Quick Links</h4>
        <ul>
          <li><a href="{{ $a('about') }}">About</a></li>
          <li><a href="{{ $a('explore') }}">Explore</a></li>
          <li><a href="{{ route('shop') }}">See All Shops</a></li>
          <li><a href="{{ route('skills') }}">Skills</a></li>
        </ul>
      </div>
      <div>
        <h4>Contact</h4>
        <ul class="contact-list">
          <li>Email: hello@heritagehub.local</li>
          <li>Phone: +880 1XXX-XXXXXX</li>
          <li>Dhaka, Bangladesh</li>
        </ul>
      </div>
    </div>
    <div class="copy">© {{ date('Y') }} Heritage Hub</div>
  </footer>
@includeIf('partials.auth-modal-fixed')

@php
  $flash = session('status') ?? session('success') ?? session('error');
@endphp
@if($flash)
  <script>
    (function(){
      const container = document.getElementById('hh-toast');
      const msg = {!! json_encode($flash) !!};
      const el = document.createElement('div');
      el.textContent = msg;
      Object.assign(el.style,{background:'#2f231d',color:'#fff',padding:'10px 14px',borderRadius:'10px',boxShadow:'0 8px 24px rgba(0,0,0,.18)'});
      container.appendChild(el);
      setTimeout(()=>el.remove(),3500);
    })();
  </script>
@endif

<script>
  // helper to show toast from other scripts
  window.hhToast = function(msg,opts={timeout:3000}){
    const container = document.getElementById('hh-toast');
    if(!container) return;
    const el = document.createElement('div');
    el.textContent = msg;
    Object.assign(el.style,{background:'#2f231d',color:'#fff',padding:'10px 14px',borderRadius:'10px',boxShadow:'0 8px 24px rgba(0,0,0,.18)',marginTop:'8px'});
    container.appendChild(el);
    setTimeout(()=>el.remove(), opts.timeout || 3000);
  }
</script>
@stack('scripts')

</body>
</html>
