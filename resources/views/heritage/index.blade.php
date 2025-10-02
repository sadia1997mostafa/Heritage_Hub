@extends('layouts.app') {{-- or your main layout --}}
@section('title', ($district ? "$district, $division" : $division) . ' — Heritage')

@section('content')
<main class="hh-container">
  <header class="hh-header">
    <nav class="hh-breadcrumb">
      <a href="{{ url('/') }}">Home</a>
      <span>›</span>
      <a href="{{ url('/map') }}">Map</a> {{-- adjust to your map page --}}
      <span>›</span>
      @if($district)
        <a href="{{ route('heritage.page', ['division' => $division]) }}">{{ $division }}</a>
        <span>›</span>
        <strong>{{ $district }}</strong>
      @else
        <strong>{{ $division }}</strong>
      @endif
    </nav>

    <div class="hh-titlebar">
      <h1>{{ $district ? "$district, $division" : $division }} — Heritage</h1>
      <div class="hh-title-actions">
        @if($district)
          <a class="hh-chip" href="{{ route('heritage.page', ['division'=>$division]) }}">View {{ $division }} (division)</a>
        @endif
        <a class="hh-chip" href="{{ route('heritage.page', ['division'=>$division, 'district'=>$district]) }}?force=1" title="Refetch from Wikipedia">Refresh</a>
        @if($cached)
          <span class="hh-badge">Cached</span>
        @else
          <span class="hh-badge live">Live</span>
        @endif
      </div>
    </div>
    <p class="hh-sub">Curated from Wikipedia for {{ $district ? "$district district of $division" : "$division division" }}.</p>
  </header>

  @if(empty($items))
    <section class="hh-empty">
      <p>No heritage items found yet.</p>
    </section>
  @else
    <section class="hh-grid">
      @foreach($items as $it)
        <article class="hh-card">
          <div class="hh-thumb" style="background-image:url('{{ $it['image_url'] ?? '' }}')"></div>
          <div class="hh-meta">
            <span class="hh-pill">{{ strtoupper($it['category'] ?? 'site') }}</span>
            <h2 class="hh-card-title">{{ $it['title'] }}</h2>
            @if(!empty($it['summary']))
              <p class="hh-card-text">{{ \Illuminate\Support\Str::limit($it['summary'], 220) }}</p>
            @endif
            <div class="hh-actions">
              @if(!empty($it['wiki_url']))
                <a href="{{ $it['wiki_url'] }}" target="_blank" rel="noopener">Wikipedia</a>
              @endif
              <a href="{{ url('/shop') }}?{{ $district ? 'district='.urlencode($district) : 'division='.urlencode($division) }}" class="ghost">See Crafts</a>
            </div>
            @if(!empty($it['lat']) && !empty($it['lon']))
              <div class="hh-coords">Lat {{ $it['lat'] }}, Lon {{ $it['lon'] }}</div>
            @endif
          </div>
        </article>
      @endforeach
    </section>
  @endif

  <footer class="hh-footnote">
    <small>Source: Wikipedia (public domain & CC content). This page caches results for up to 30 days.</small>
  </footer>
</main>
@endsection

@push('styles')
<style>
.hh-container{max-width:1100px;margin:0 auto;padding:20px}
.hh-header{margin-bottom:14px}
.hh-breadcrumb{display:flex;gap:8px;align-items:center;font-size:14px}
.hh-breadcrumb a{text-decoration:none;color:#6b4b23}
.hh-titlebar{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap}
.hh-titlebar h1{margin:6px 0;color:#5a3a12;font-family:'Cinzel',serif}
.hh-title-actions{display:flex;gap:8px;align-items:center}
.hh-chip{border:1px solid #b68b46;border-radius:999px;padding:6px 10px;text-decoration:none;color:#5a3a12;background:#fff7eb;font-size:13px}
.hh-badge{font-size:12px;padding:3px 8px;border-radius:999px;background:#f0e1c9;color:#6b4b23;border:1px solid #b68b46}
.hh-badge.live{background:#e2f7e2;border-color:#7bbf7b;color:#2a6f2a}
.hh-sub{margin:4px 0 12px;color:#5b5146}

.hh-empty{padding:24px;border:1px dashed #c6a36b;border-radius:12px;background:#fffaf1;color:#6b4b23}

.hh-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px}
.hh-card{display:grid;grid-template-rows:160px auto;background:#f8f2e8;border:1px solid #c6a36b;border-radius:14px;overflow:hidden;box-shadow:0 6px 18px rgba(90,60,20,.08)}
.hh-thumb{background-size:cover;background-position:center}
.hh-meta{padding:12px}
.hh-pill{display:inline-block;font-size:11px;letter-spacing:.08em;color:#6b4b23;background:#f0e1c9;border:1px solid #b68b46;padding:2px 8px;border-radius:999px;margin-bottom:6px}
.hh-card-title{margin:4px 0 8px;color:#5a3a12;font-size:18px}
.hh-card-text{margin:0 0 10px;color:#5b5146;font-size:14px;line-height:1.4}
.hh-actions{display:flex;gap:10px}
.hh-actions a{font-size:13px;text-decoration:none;border:1px solid #b68b46;padding:6px 10px;border-radius:10px;color:#5a3a12;background:#fff7eb}
.hh-actions a.ghost{background:transparent}
.hh-coords{margin-top:10px;font-size:12px;color:#6b4b23}
.hh-footnote{margin-top:20px;color:#6b4b23}
</style>
@endpush
