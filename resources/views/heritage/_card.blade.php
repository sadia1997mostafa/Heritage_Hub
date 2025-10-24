<article class="hh-card" aria-labelledby="item-{{ $item['id'] ?? ($item->id ?? 'x') }}-title">
  @php
    $hero = $item['hero_image'] ?? ($item->hero_image ?? null);
    $title = $item['title'] ?? ($item->title ?? 'Untitled');
  @endphp
  @if($hero)
    <div class="hh-thumb" role="img" aria-label="Image of {{ e($title) }}" style="background-image:url('{{ $hero }}')"></div>
  @else
    <div class="hh-thumb" role="img" aria-label="No image available" style="background-color:#efe6d8"></div>
  @endif

  <div class="hh-meta">
    <h3 id="item-{{ $item['id'] ?? ($item->id ?? 'x') }}-title">{{ $title }}</h3>
    @if(!empty($item['location'] ?? $item->location ?? null))
      <p class="hh-loc">{{ $item['location'] ?? $item->location }}</p>
    @endif
    @if(!empty($item['summary'] ?? $item->summary ?? null))
      <p>{{ Str::limit($item['summary'] ?? $item->summary, 220) }}</p>
    @endif
    {{-- optional badges --}}
    @if(!empty($item['category'] ?? $item->category ?? null))
      <div style="margin-top:8px"><span class="hh-badge">{{ ucfirst($item['category'] ?? $item->category) }}</span></div>
    @endif
  </div>
</article>
