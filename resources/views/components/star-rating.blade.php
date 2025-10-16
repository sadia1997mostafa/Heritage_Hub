@props(['rating'=>0,'size'=>14,'class'=>'','showCount'=>false,'count'=>0])

@php
  // clamp rating between 0 and 5
  $r = max(0, min(5, floatval($rating)));
  $pct = ($r / 5) * 100; // overall percent
  // For each star, compute fill percent (0-100)
  $stars = [];
  for ($i = 1; $i <= 5; $i++) {
    $starFill = max(0, min(100, ($r - ($i - 1)) * 100));
    if ($starFill < 0) $starFill = 0;
    if ($starFill > 100) $starFill = 100;
    $stars[] = $starFill;
  }
@endphp

<div class="star-rating {{ $class }}" style="--star-size:{{ (int)$size }}px;">
  @foreach($stars as $fill)
    <span class="star" style="--fill:{{ $fill }}%" aria-hidden="true">
      <svg viewBox="0 0 20 20" fill="currentColor" class="star-svg" width="{{ $size }}" height="{{ $size }}"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.966a1 1 0 00.95.69h4.18c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.286 3.966c.3.921-.755 1.688-1.54 1.118l-3.385-2.46a1 1 0 00-1.176 0l-3.385 2.46c-.785.57-1.84-.197-1.54-1.118l1.286-3.966a1 1 0 00-.364-1.118L2.03 9.393c-.783-.57-.38-1.81.588-1.81h4.18a1 1 0 00.95-.69l1.286-3.966z"/></svg>
    </span>
  @endforeach
  @if($showCount)
    <span class="star-count text-xs text-gray-400 ml-2">{{ $rating }} ({{ $count }})</span>
  @endif
</div>
