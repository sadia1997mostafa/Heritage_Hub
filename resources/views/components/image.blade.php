@php
  // path may be a storage path like 'vendors/logo.png' or full URL
  $src = \Illuminate\Support\Str::startsWith($path, ['http://','https://']) ? $path : asset('storage/'.$path);
  $sizes = $sizes ?? '(max-width:480px) 160px, 320px';
  $candidateSizes = [320,640,1024];
  $entries = [];
  foreach($candidateSizes as $w) {
    $candidate = preg_replace('/\.[^.]+$/', "-{$w}.webp", $path);
    $full = public_path('storage/'.$candidate);
    if (file_exists($full)) { $entries[$w] = asset('storage/'.$candidate); }
  }
@endphp

<img src="{{ $src }}"
     @if(!empty($entries)) srcset="@foreach($entries as $w=>$u){{ $u }} {{ $w }}w,@endforeach" sizes="{{ $sizes }}" @endif
     alt="{{ $alt }}" loading="lazy" decoding="async" />
