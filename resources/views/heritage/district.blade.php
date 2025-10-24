@extends('layouts.app')
@section('title', $district->name . ' District')

@section('content')
<main class="hh-wrap">
  {{-- Banner (image output commented out — add banner later) --}}
  {{-- original banner image attribute: style="background-image:url('{{ $district->banner_url }}')" --}}
  <section class="hh-banner no-image">
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

    @foreach(['site','craft','festival','cuisine'] as $tab)
      <div class="hh-tabpanel {{ $loop->first ? 'active' : '' }}" data-tabpanel="{{ $tab }}">
        @php $items = $itemsByCat[$tab] ?? []; @endphp
        @if(empty($items))
          <p class="hh-empty">No entries yet.</p>
        @else
          <div class="hh-grid">
            @foreach($items as $it)
              @php
                // choose image: hero_image or first media image of type 'image'
                $img = $it->hero_image ?? (isset($it->media) && $it->media->count() ? ($it->media->where('type','image')->first()->url ?? null) : null);
                $imgUrl = null;
                if ($img) {
                  if (preg_match('/^https?:\/\//i', $img)) {
                    $imgUrl = $img;
                  } else {
                    $imgUrl = asset('storage/' . ltrim($img, '/'));
                  }
                }
              @endphp
              <article class="hh-card">
                @php
                  // Normalize image URL for secure pages to avoid mixed-content blocking
                  $imgSrc = $imgUrl;
                  if ($imgSrc && request()->isSecure() && preg_match('/^http:/i', $imgSrc)) {
                    $imgSrc = preg_replace('/^http:/i', 'https:', $imgSrc);
                  }
                @endphp

                @if(!empty($imgSrc))
                  <div class="hh-thumb">
                    <img
                      src="{{ $imgSrc }}"
                      alt="{{ e($it->title) }}"
                      style="width:100%;height:100%;object-fit:cover;display:block;"
                      onerror="this.style.display='none';"
                    />
                  </div>
                @else
                  <div class="hh-thumb"></div>
                @endif
                <div class="hh-meta">
                  <h3>{{ $it->title }}</h3>
                  @if($it->location)<p class="hh-loc">{{ $it->location }}</p>@endif
                  @if($it->summary)<p>{{ $it->summary }}</p>@endif
                </div>
              </article>
            @endforeach
          </div>
        @endif
      </div>
    @endforeach
  </section>

  {{-- Gallery --}}
  <section class="hh-gallery">
    <h2>Gallery</h2>
    @if(empty($gallery))
      <p class="hh-empty">No images yet.</p>
    @else
      <div class="hh-gallery-grid">
        @foreach($gallery as $g)
          @php
            $gUrl = $g['url'] ?? null;
            if ($gUrl && !preg_match('/^https?:\/\//i', $gUrl)) {
              $gUrl = asset('storage/' . ltrim($gUrl, '/'));
            }
          @endphp
          <figure>
            @if(!empty($gUrl))
              <img src="{{ $gUrl }}" alt="{{ $g['caption'] ?? 'Photo' }}">
            @endif
            @if(!empty($g['caption']))<figcaption>{{ $g['caption'] }}</figcaption>@endif
          </figure>
        @endforeach
      </div>
    @endif
  </section>

  {{-- Sources removed intentionally to simplify the district page. --}}

  {{-- Explore Local Vendors (show only vendors that match this district) --}}
  <section class="hh-vendors">
    <h2>Explore Local Vendors</h2>

    @php
      // Start with any vendors already eager-loaded on the district
      $vendors = $district->vendors ?? collect();

      // If none, attempt a safe fallback query matching by district_id or district name.
      if (empty($vendors) || $vendors->isEmpty()) {
        try {
          // Prefer legacy Vendor table if it exists
          if (class_exists('\App\\Models\\Vendor')) {
            $vendors = \App\Models\Vendor::where('district_id', $district->id)
              ->orWhere('district', $district->name)
              ->get();
          }

          // Also include VendorProfile entries where applicable (newer storefront).
          // Merge results so both legacy vendors and vendor_profiles show on the district page.
          if (class_exists('\App\\Models\\VendorProfile')) {
            $vp = \App\Models\VendorProfile::where(function($q) use ($district) {
              $q->where('district_id', $district->id)
                ->orWhere('shop_name', 'like', '%' . $district->name . '%')
                ->orWhere('address', 'like', '%' . $district->name . '%');
            })->get();

            if ($vp && $vp->isNotEmpty()) {
              // If $vendors is empty, use the VendorProfile collection; otherwise merge them.
              $vendors = (empty($vendors) || $vendors->isEmpty()) ? $vp : $vendors->merge($vp);
            }
          }

          // ensure we have a collection
          if (empty($vendors)) $vendors = collect();
        } catch (\Throwable $e) {
          // On any error (missing table/column), fall back to empty collection
          $vendors = collect();
        }
      }
    @endphp

    @if($vendors->isEmpty())
      <p class="hh-empty">No vendors yet.</p>
    @else
      <div class="hh-grid">
        @foreach($vendors as $v)
          @php
            // Support both legacy `Vendor` and newer `VendorProfile` attribute names.
            $displayName = $v->name ?? $v->shop_name ?? 'Shop';
            $logoUrl = null;

            // VendorProfile may expose a `logo_url` accessor; legacy Vendor uses `logo_url` column.
            if (isset($v->logo_url) && $v->logo_url) {
              $logo = $v->logo_url;
            } elseif (isset($v->shop_logo_path) && $v->shop_logo_path) {
              $logo = asset('storage/' . ltrim($v->shop_logo_path, '/'));
            } else {
              $logo = null;
            }

            if ($logo) {
              if (preg_match('/^https?:\/\//i', $logo)) {
                $logoUrl = $logo;
              } else {
                $logoUrl = $logo; // already asset() when shop_logo_path used
              }
            }

            $tags = $v->tags ?? $v->vendor_category ?? null;
            $desc = $v->description ?? $v->heritage_story ?? null;
            $shopUrl = $v->shop_url ?? null;
            $websiteUrl = $v->website_url ?? null;
          @endphp

          <article class="hh-card vendor">
            @if($logoUrl)
              <div class="hh-thumb"><img src="{{ $logoUrl }}" alt="{{ e($displayName) }}"></div>
            @else
              <div class="hh-thumb"></div>
            @endif
            <div class="hh-meta">
              <h3>{{ $displayName }}</h3>
              @if(!empty($tags))<p class="hh-tags">{{ $tags }}</p>@endif
              @if(!empty($desc))<p>{{ $desc }}</p>@endif
              <div class="hh-actions">
                @if(!empty($shopUrl))<a href="{{ $shopUrl }}">Shop</a>@endif
                @if(!empty($websiteUrl))<a href="{{ $websiteUrl }}" target="_blank" rel="noopener">Website</a>@endif
              </div>
            </div>
          </article>
        @endforeach
      </div>
    @endif
  </section>
</main>
@endsection

{{-- Chat widget (fast demo: client-side OpenAI calls) --}}
<div id="hh-chat-root">
  <button id="hh-chat-open" title="Ask about this district">💬 Ask</button>
  <div id="hh-chat-modal" aria-hidden="true">
    <div class="hh-chat-panel">
      <header>
        <strong>District Chat — {{ $district->name }}</strong>
        <button id="hh-chat-close">✕</button>
      </header>
      <section class="hh-chat-body" id="hh-chat-body"></section>
      <footer>
        <input id="hh-api-key" placeholder="Paste OpenAI API key (sk-...)" />
        <input id="hh-chat-input" placeholder="Ask a question about this district" />
        <button id="hh-chat-send">Send</button>
      </footer>
      <div class="hh-chat-note">Note: this demo calls OpenAI directly from your browser; do not paste production secrets here.</div>
    </div>
  </div>
</div>

@push('styles')
<style>
#hh-chat-root { position: fixed; right: 18px; bottom: 18px; z-index:13000 }
#hh-chat-open { background:#b68b46;color:#fff;border:none;padding:10px 14px;border-radius:50%;box-shadow:0 8px 20px rgba(0,0,0,.12);cursor:pointer }
#hh-chat-modal{ position:fixed; right:18px; bottom:78px; width:360px; max-width:92vw; display:none }
.hh-chat-panel{ background:#fff;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,.25);overflow:hidden;display:flex;flex-direction:column;height:520px }
.hh-chat-panel header{ padding:12px 14px;background:linear-gradient(90deg,#f7efe0,#fff);display:flex;justify-content:space-between;align-items:center }
.hh-chat-body{ padding:12px; overflow:auto; flex:1; background:#fcfbf8 }
.hh-chat-body .msg{ margin-bottom:10px }
.hh-chat-body .msg.user{ text-align:right }
.hh-chat-body .msg .bubble{ display:inline-block;padding:8px 12px;border-radius:12px;max-width:86% }
.hh-chat-body .msg.user .bubble{ background:#e6e6e6 }
.hh-chat-body .msg.bot .bubble{ background:#fff7ec;border:1px solid #f0d9b0 }
.hh-chat-panel footer{ padding:10px; display:flex; gap:8px; align-items:center; background:#fff }
.hh-chat-panel footer input[type="text"], .hh-chat-panel footer input[type="password"]{ flex:1;padding:8px;border:1px solid #e6d3a8;border-radius:8px }
.hh-chat-note{ font-size:12px;color:#7a5a3a;padding:8px 12px }
#hh-api-key{ font-size:12px }
</style>
@endpush

@push('scripts')
<script>
(function(){
  const slug = '{{ $district->slug }}';
  const csrf = '{{ csrf_token() }}';
  let districtData = null;

  async function fetchDistrict(){
    try {
      const res = await fetch(`/api/districts/${encodeURIComponent(slug)}`);
      if (!res.ok) return null;
      const json = await res.json();
      districtData = json.data || json;
      return districtData;
    } catch(e){ return null }
  }

  // UI refs
  const openBtn = document.getElementById('hh-chat-open');
  const modal = document.getElementById('hh-chat-modal');
  const closeBtn = document.getElementById('hh-chat-close');
  const bodyEl = document.getElementById('hh-chat-body');
  const input = document.getElementById('hh-chat-input');
  const sendBtn = document.getElementById('hh-chat-send');
  const apiKeyEl = document.getElementById('hh-api-key');

  function appendMsg(who, text){
    const div = document.createElement('div'); div.className = 'msg ' + (who==='user'?'user':'bot');
    const bubble = document.createElement('div'); bubble.className = 'bubble'; bubble.textContent = text;
    div.appendChild(bubble); bodyEl.appendChild(div); bodyEl.scrollTop = bodyEl.scrollHeight;
  }

  openBtn.addEventListener('click', async () => {
    modal.style.display = 'block'; modal.setAttribute('aria-hidden','false');
    if (!districtData) {
      appendMsg('bot','Loading district data...');
      await fetchDistrict();
      appendMsg('bot','District data loaded. You can ask me anything about this district.');
    }
  });
  closeBtn.addEventListener('click', ()=>{ modal.style.display='none'; modal.setAttribute('aria-hidden','true'); bodyEl.innerHTML=''; districtData=null });

  async function askOpenAI(question){
    appendMsg('user', question);
    appendMsg('bot','Thinking...');

    // Try server-side proxy first
    try {
      const resp = await fetch(`/district/${encodeURIComponent(slug)}/chat`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ question })
      });
      const j = await resp.json();
      const last = bodyEl.querySelector('.msg.bot:last-child'); if (last) last.remove();
      if (!resp.ok) {
        // If server says no key configured and user provided a key, fallback to client direct call
        if (j && j.error && /not configured/i.test(j.error) && apiKeyEl.value.trim()) {
          return askDirectOpenAI(question, apiKeyEl.value.trim());
        }
        return appendMsg('bot', 'Server error: ' + (j.message || j.error || JSON.stringify(j)));
      }
      if (j.reply) {
        return appendMsg('bot', j.reply);
      }
      return appendMsg('bot', 'No reply from server.');
    } catch (err) {
      const last = bodyEl.querySelector('.msg.bot:last-child'); if (last) last.remove();
      // network error — fallback to direct browser call if key available
      if (apiKeyEl.value.trim()) {
        return askDirectOpenAI(question, apiKeyEl.value.trim());
      }
      appendMsg('bot', 'Request failed: ' + err.message);
    }
  }

  // Direct client-side OpenAI call fallback
  async function askDirectOpenAI(question, key){
    appendMsg('bot','Thinking...');
    const contextParts = [];
    if (districtData && districtData.district) {
      contextParts.push(`District: ${districtData.district.name}. Intro: ${districtData.district.intro_html || ''}`);
    }
    if (districtData && districtData.itemsByCat) {
      Object.keys(districtData.itemsByCat).forEach(cat => {
        const items = districtData.itemsByCat[cat] || [];
        if (items.length) {
          contextParts.push(`${cat} items: ${items.slice(0,6).map(i=>i.title).join(', ')}`);
        }
      });
    }
    const system = `You are a helpful assistant that answers questions about a district. Use ONLY the context provided. If answer is not in context, say you don't know and offer suggestions.`;
    const userPrompt = `Context:\n${contextParts.join('\n')}\n\nQuestion: ${question}`;
    try {
      const resp = await fetch('https://api.openai.com/v1/chat/completions', {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'Authorization':'Bearer ' + key },
        body: JSON.stringify({ model:'gpt-3.5-turbo', messages:[{role:'system',content:system},{role:'user',content:userPrompt}], max_tokens:512 })
      });
      const j = await resp.json();
      const last = bodyEl.querySelector('.msg.bot:last-child'); if (last) last.remove();
      if (j.error) { appendMsg('bot', 'OpenAI error: ' + (j.error.message || JSON.stringify(j.error))); return; }
      const text = j.choices && j.choices[0] && j.choices[0].message && j.choices[0].message.content ? j.choices[0].message.content.trim() : JSON.stringify(j);
      appendMsg('bot', text);
    } catch(err){
      const last = bodyEl.querySelector('.msg.bot:last-child'); if (last) last.remove();
      appendMsg('bot', 'Request failed: ' + err.message);
    }
  }

  sendBtn.addEventListener('click', ()=>{ const q = input.value.trim(); if (!q) return; input.value=''; askOpenAI(q); });
  input.addEventListener('keydown', (e)=>{ if (e.key==='Enter') { e.preventDefault(); sendBtn.click(); } });

})();
</script>
@endpush

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
