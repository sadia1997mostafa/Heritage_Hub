@extends('layouts.app')

@section('content')
<div class="container">
    <div class="event-card event-show" style="max-width:900px;margin:0 auto">
        @if($event->cover_image)
            <div style="position:relative">
                <img src="{{ asset('storage/'.$event->cover_image) }}" class="event-cover" alt="{{ $event->title }}">
                <div style="position:absolute;left:18px;bottom:18px;color:#fff;text-shadow:0 6px 18px rgba(0,0,0,.45);">
                    <h1 class="event-title" style="margin:0;color:#fff">{{ $event->title }}</h1>
                    <div style="font-size:14px;color:rgba(255,255,255,.95);margin-top:6px">{{ $event->starts_at ? $event->starts_at->format('M j, H:i') : '' }} {{ $event->ends_at ? '– '.$event->ends_at->format('M j, H:i') : '' }}</div>
                </div>
            </div>
        @else
            <div class="event-cover" aria-hidden="true"></div>
        @endif

        <div class="event-body">
            <div class="event-meta" style="align-items:center">
                <div>by {{ $event->owner->name ?? 'Unknown' }}</div>
                <div style="flex:1"></div>
                <div class="event-badge">{{ $event->starts_at ? $event->starts_at->format('M j') : '' }} {{ $event->ends_at ? '– '.$event->ends_at->format('M j') : '' }}</div>
            </div>

            <div class="event-desc" style="margin-top:12px">{!! nl2br(e($event->description)) !!}</div>

            <div class="mt-3" style="display:flex;gap:10px;align-items:center">
                <form method="POST" action="{{ route('events.rsvp', $event) }}" class="event-actions" data-event-id="{{ $event->id }}">
                    @csrf
                    <button type="submit" name="status" value="interested" class="btn-ghost rsvp-btn">☆ Interested <span class="rsvp-count">({{ $event->interestedCount() }})</span></button>
                    <button type="submit" name="status" value="going" class="btn-going rsvp-btn">✔ Going <span class="rsvp-count">({{ $event->goingCount() }})</span></button>
                </form>

                <a href="{{ url()->previous() ?: route('events.index') }}" class="btn btn-sm btn-link" style="margin-left:auto">Back to events</a>
            </div>

            {{-- attendees rendered via partial for consistent layout --}}
            <div class="mt-3">
                @include('events._attendees', ['event' => $event])
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
    @php
        $__hh_manifest_path = public_path('build/manifest.json');
        $__hh_found = false;
        if (file_exists($__hh_manifest_path)) {
            try {
                $__hh_manifest = json_decode(file_get_contents($__hh_manifest_path), true);
                foreach ($__hh_manifest as $__hh_k => $__hh_v) {
                    if (is_array($__hh_v)) {
                        $file = $__hh_v['file'] ?? '';
                        $src = $__hh_v['src'] ?? $__hh_k;
                        if (str_contains($file, 'theme') || str_contains($src, 'theme')) {
                            echo '<link rel="stylesheet" href="' . asset('build/' . $file) . '">';
                            $__hh_found = true; break;
                        }
                    }
                }
            } catch (\Throwable $__hh_e) {
                // ignore
            }
        }

        if (! $__hh_found) {
            if (file_exists(public_path('build/assets/themes-DkJX4bC.css'))) {
                echo '<link rel="stylesheet" href="' . asset('build/assets/themes-DkJX4bC.css') . '">';
            } else {
                $__hh_local = resource_path('css/theme-events.css');
                if (file_exists($__hh_local)) {
                    try { $c = file_get_contents($__hh_local); if (!empty(trim($c))) echo '<style>' . PHP_EOL . $c . PHP_EOL . '</style>'; } catch (\Throwable $__hh_e) {}
                }
            }
        }
    @endphp
@endpush

@push('styles')
<style id="event-show-inline">
/* Inline show-page styles (self-contained) */
.event-show{max-width:980px;margin:24px auto;border-radius:18px;overflow:hidden;background:linear-gradient(180deg,#6f4a2e,#5d3d27);box-shadow:0 18px 36px rgba(0,0,0,.28);border:1px solid rgba(217,137,64,.12);}
.event-cover-wrap{position:relative}
.event-cover{width:100%;height:clamp(240px,34vw,380px);object-fit:cover;display:block}
.event-cover-overlay{position:absolute;left:18px;bottom:18px;color:#fff;text-shadow:0 10px 28px rgba(0,0,0,.6);z-index:2}
.event-cover-overlay .event-title{font-size:30px;margin:0;line-height:1.05;font-weight:800}
.event-cover-overlay .event-dates{font-size:13px;margin-top:6px;opacity:.94}
.event-body{padding:18px 20px 22px;background:linear-gradient(180deg,rgba(255,255,255,.02),rgba(0,0,0,.02));color:#F8EEE3}
.event-meta{display:flex;align-items:center;gap:10px;color:#F1E4D4}
.event-desc{margin-top:12px;color:#F6EADB;font-size:15px;line-height:1.6}
.actions-row{display:flex;gap:12px;align-items:center;margin-top:12px}
.event-actions{display:flex;gap:12px;align-items:center}
.btn-ghost,.btn-going{display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:12px;font-weight:800;cursor:pointer;border:1px solid rgba(255,255,255,.12);transition:transform .12s ease,box-shadow .14s ease}
.btn-ghost{background:rgba(255,255,255,.06);color:#fff}
.btn-going{background:linear-gradient(180deg,#e5ba73,#c58940);color:#2b1c11}
.btn-ghost.active{background:linear-gradient(180deg,rgba(139,94,60,.35),rgba(139,94,60,.18));color:#ffe7d2}
.btn-going.active{box-shadow:0 10px 24px rgba(181,132,63,.28);}
.btn-back{margin-left:auto;color:#E5BA73;font-weight:700;text-decoration:underline}
.attendees{display:flex;gap:8px;align-items:center;margin-top:12px}
.attendees img{width:40px;height:40px;border-radius:999px;object-fit:cover;border:2px solid #fff;box-shadow:0 10px 26px rgba(0,0,0,.32)}
@media(max-width:900px){.event-cover-overlay .event-title{font-size:24px}.event-cover{height:300px}.event-show{margin:18px}}
@media(max-width:480px){.event-cover-overlay{left:12px;bottom:12px}.event-cover-overlay .event-title{font-size:20px}.actions-row{flex-direction:column;align-items:flex-start}.btn-back{margin-left:0}}
</style>
@endpush
