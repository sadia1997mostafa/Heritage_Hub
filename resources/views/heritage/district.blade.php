@extends('layouts.app')
@section('title', $district->name . ' District')

@section('content')
<main class="hh-wrap">
  {{-- Banner --}}
  <section class="hh-banner" style="background-image:url('{{ $district->banner_url }}')">
    <div class="hh-banner-overlay">
      <h1>{{ $district->name }} District</h1>
      <p class="hh-division">Division: {{ $division->name }}</p>
    </div>
  </section>

  {{-- Intro --}}
  <section class="hh-intro">
    @if($district->intro_html)
      {!! $district->intro_html !!}
    @else
      <p>Discover heritage sites, crafts, festivals, cuisine, and local vendors from {{ $district->name }}.</p>
    @endif
  </section>

  {{-- Tabs / Accordion --}}
  <section class="hh-tabs">
    <nav class="hh-tablist" role="tablist">
      <button class="hh-tab active" data-tab="site">Heritage Sites</button>
      <button class="hh-tab" data-tab="craft">Crafts</button>
      <button class="hh-tab" data-tab="festival">Festivals</button>
      <button class="hh-tab" data-tab="cuisine">Cuisine</button>
    </nav>

    @php
      $renderCards = function($items) {
        if (empty($items)) return '<p class="hh-empty">No entries yet.</p>';
        $out = '<div class="hh-grid">';
        foreach ($items as $it) {
          $out .= '<article class="hh-card">';
          $out .= '<div class="hh-thumb" style="background-image:url(\''.e($it->hero_image ?? '').'\')"></div>';
          $out .= '<div class="hh-meta">';
          $out .= '<h3>'.e($it->title).'</h3>';
          if ($it->location) $out .= '<p class="hh-loc">'.e($it->location).'</p>';
          if ($it->summary)  $out .= '<p>'.e($it->summary).'</p>';
          $out .= '</div></article>';
        }
        $out .= '</div>';
        return $out;
      };
    @endphp

    <div class="hh-tabpanel active" data-tabpanel="site">{!! $renderCards($itemsByCat['site']) !!}</div>
    <div class="hh-tabpanel" data-tabpanel="craft">{!! $renderCards($itemsByCat['craft']) !!}</div>
    <div class="hh-tabpanel" data-tabpanel="festival">{!! $renderCards($itemsByCat['festival']) !!}</div>
    <div class="hh-tabpanel" data-tabpanel="cuisine">{!! $renderCards($itemsByCat['cuisine']) !!}</div>
  </section>

  {{-- Gallery --}}
  <section class="hh-gallery">
    <h2>Gallery</h2>
    @if(empty($gallery))
      <p class="hh-empty">No images yet.</p>
    @else
      <div class="hh-gallery-grid">
        @foreach($gallery as $g)
          <figure>
            <img src="{{ $g['url'] }}" alt="{{ $g['caption'] ?? 'Photo' }}">
            @if(!empty($g['caption']))<figcaption>{{ $g['caption'] }}</figcaption>@endif
          </figure>
        @endforeach
      </div>
    @endif
  </section>

  {{-- Sources --}}
  <section class="hh-sources">
    <h2>Sources</h2>
    @if($district->sources->isEmpty())
      <p class="hh-empty">No sources listed.</p>
    @else
      <ul>
        @foreach($district->sources as $s)
          <li>
            @if($s->url)
              <a href="{{ $s->url }}" target="_blank" rel="noopener">{{ $s->title }}</a>
            @else
              {{ $s->title }}
            @endif
          </li>
        @endforeach
      </ul>
    @endif
  </section>

  {{-- Explore Local Vendors --}}
  <section class="hh-vendors">
    <h2>Explore Local Vendors</h2>
    @if($district->vendors->isEmpty())
      <p class="hh-empty">No vendors yet.</p>
    @else
      <div class="hh-grid">
        @foreach($district->vendors as $v)
          <article class="hh-card vendor">
            <div class="hh-thumb" style="background-image:url('{{ $v->logo_url }}')"></div>
            <div class="hh-meta">
              <h3>{{ $v->name }}</h3>
              @if($v->tags)<p class="hh-tags">{{ $v->tags }}</p>@endif
              @if($v->description)<p>{{ $v->description }}</p>@endif
              <div class="hh-actions">
                @if($v->shop_url)<a href="{{ $v->shop_url }}">Shop</a>@endif
                @if($v->website_url)<a href="{{ $v->website_url }}" target="_blank" rel="noopener">Website</a>@endif
              </div>
            </div>
          </article>
        @endforeach
      </div>
    @endif
  </section>
</main>
@endsection

@push('styles')
<style>
.hh-wrap{max-width:1120px;margin:0 auto;padding:16px}
.hh-banner{height:280px;background-size:cover;background-position:center;border-radius:16px;position:relative;overflow:hidden;margin-bottom:16px}
.hh-banner-overlay{position:absolute;inset:0;background:linear-gradient(180deg,rgba(0,0,0,.35),rgba(0,0,0,.35));display:flex;flex-direction:column;justify-content:flex-end;padding:18px}
.hh-banner h1{color:#fff;margin:0}
.hh-division{color:#ffd; margin:4px 0 0}

.hh-intro{background:#fff8ee;border:1px solid #e9d4b4;border-radius:12px;padding:14px;margin-bottom:16px}
.hh-tabs{margin-bottom:16px}
.hh-tablist{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px}
.hh-tab{border:1px solid #c6a36b;background:#fff7eb;color:#5a3a12;border-radius:999px;padding:6px 12px;cursor:pointer}
.hh-tab.active{background:#f0e1c9}
.hh-tabpanel{display:none}
.hh-tabpanel.active{display:block}

.hh-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px}
.hh-card{background:#f8f2e8;border:1px solid #c6a36b;border-radius:14px;overflow:hidden;box-shadow:0 6px 18px rgba(90,60,20,.08)}
.hh-card.vendor .hh-thumb{background-size:contain;background-repeat:no-repeat;background-position:center;background-color:#fff}
.hh-thumb{height:150px;background-size:cover;background-position:center}
.hh-meta{padding:10px}
.hh-loc{font-size:12px;color:#6b4b23;margin-top:-4px}
.hh-empty{color:#6b4b23}
.hh-gallery{margin:16px 0}
.hh-gallery-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px}
.hh-gallery-grid img{width:100%;height:140px;object-fit:cover;border-radius:10px;border:1px solid #e9d4b4}
.hh-sources ul{padding-left:18px}
.hh-actions{display:flex;gap:8px;margin-top:8px}
.hh-actions a{border:1px solid #b68b46;padding:6px 10px;border-radius:10px;text-decoration:none;color:#5a3a12;background:#fff7eb}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const tabs = [...document.querySelectorAll('.hh-tab')];
  const panels = [...document.querySelectorAll('.hh-tabpanel')];
  tabs.forEach(btn => {
    btn.addEventListener('click', () => {
      const name = btn.dataset.tab;
      tabs.forEach(t => t.classList.toggle('active', t === btn));
      panels.forEach(p => p.classList.toggle('active', p.dataset.tabpanel === name));
    });
  });
});
</script>
@endpush
