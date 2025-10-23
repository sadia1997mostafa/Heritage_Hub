@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Events</h2>
        <div>
            <a href="{{ route('events.index', ['tab'=>'all']) }}" class="btn btn-sm {{ $tab === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">All Events</a>
            <a href="{{ route('events.index', ['tab'=>'mine']) }}" class="btn btn-sm {{ $tab === 'mine' ? 'btn-primary' : 'btn-outline-secondary' }}">My Events</a>
        </div>
    </div>

    @if($tab === 'mine')
        <div class="mb-3">
            @auth
            <!-- Create button toggles form -->
            <button class="btn btn-success" onclick="document.getElementById('create-event-form').classList.toggle('d-none')">+ Create Event</button>
            <div id="create-event-form" class="mt-3 d-none">
                <form method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-2">
                        <input name="title" class="form-control" placeholder="Event title" required>
                    </div>
                    <div class="mb-2">
                        <textarea name="description" class="form-control" placeholder="Description"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Starts at</label>
                        <input id="starts_at" type="datetime-local" name="starts_at" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-check">
                            <input id="has_end" type="checkbox" class="form-check-input"> <span class="form-check-label">Add end time</span>
                        </label>
                        <div id="ends_wrapper" class="mt-2 d-none">
                            <label class="form-label">Ends at</label>
                            <input id="ends_at" type="datetime-local" name="ends_at" class="form-control">
                        </div>
                    </div>
                    <div>
                        <input name="location" class="form-control" placeholder="Location">
                    </div>
                    <div class="mt-2">
                        <label class="form-label">Cover image (optional)</label>
                        <input type="file" name="cover_image" accept="image/*" class="form-control-file" />
                    </div>
                    <div class="mt-2">
                        <button class="btn btn-primary">Create</button>
                    </div>
                </form>
            </div>
            @else
                <a href="{{ route('login') }}" class="btn btn-sm btn-outline-primary">Login to create events</a>
            @endauth
        </div>
    @endif

    <div class="events-grid">
        @foreach($events as $event)
        <div class="event-card">
            @if($event->cover_image)
                <div style="position:relative">
                    <img src="{{ asset('storage/'.$event->cover_image) }}" class="event-cover" alt="{{ $event->title }}">
                    <div style="position:absolute;left:12px;bottom:12px;color:#fff;text-shadow:0 6px 18px rgba(0,0,0,.45);">
                        <h3 class="event-title" style="color: #fff">{{ $event->title }}</h3>
                        <div style="font-size:13px;color:rgba(255,255,255,.9);">{{ $event->starts_at ? $event->starts_at->format('M j, H:i') : '' }} {{ $event->ends_at ? '– '.$event->ends_at->format('M j, H:i') : '' }}</div>
                    </div>
                </div>
            @else
                <div class="event-cover" aria-hidden="true"></div>
                <div class="event-body">
                    <h3 class="event-title">{{ $event->title }}</h3>
            @endif
                <div class="event-meta">
                    <div>by {{ $event->owner->name ?? 'Unknown' }}</div>
                    <div style="flex:1"></div>
                    <div class="event-badge">{{ $event->starts_at ? $event->starts_at->format('M j') : '' }} {{ $event->ends_at ? '– '.$event->ends_at->format('M j') : '' }}</div>
                </div>
                <p class="event-desc">{{ Str::limit($event->description,160) }}</p>
                <form method="POST" action="{{ route('events.rsvp', $event) }}" class="event-actions" data-event-id="{{ $event->id }}">
                    @csrf
                    <button type="submit" name="status" value="interested" class="btn-ghost rsvp-btn" data-status="interested">☆ Interested <span class="rsvp-count">({{ $event->interestedCount() }})</span></button>
                    <button type="submit" name="status" value="going" class="btn-going rsvp-btn" data-status="going">✔ Going <span class="rsvp-count">({{ $event->goingCount() }})</span></button>
                    <a href="{{ route('events.show', $event) }}" class="btn btn-sm btn-link">Details</a>
                </form>
                <div class="mt-2 attendees" style="display:flex;gap:6px;align-items:center">
                    @foreach($event->attendees->take(6) as $att)
                        <img src="{{ $att->avatar_url }}" alt="{{ $att->name }}" title="{{ $att->name }}" style="width:28px;height:28px;border-radius:999px;object-fit:cover;border:2px solid #fff;box-shadow:0 6px 18px rgba(0,0,0,.12)">
                    @endforeach
                    @if($event->attendees->count() > 6)
                        <div class="event-badge">+{{ $event->attendees->count() - 6 }}</div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-3">
        {{ $events->withQueryString()->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
// Initialize RSVP handlers even if this script is loaded after DOMContentLoaded
function __hh_init_rsvp(){
    // tiny toast helper for instant feedback
    function hhToastMsg(msg, timeout=2000){
        const container = document.getElementById('hh-toast');
        const el = document.createElement('div');
        el.textContent = msg;
        Object.assign(el.style,{background:'#2f231d',color:'#fff',padding:'8px 12px',borderRadius:'8px',boxShadow:'0 10px 24px rgba(0,0,0,.18)',marginTop:'6px'});
        container.appendChild(el);
        setTimeout(()=>el.remove(), timeout);
    }

    // Event delegation: handle clicks on any current or future .rsvp-btn
    document.body.addEventListener('click', async function(e){
        const btn = e.target.closest('.rsvp-btn');
        if (!btn) return;
        e.preventDefault();

        const wrapper = btn.closest('.event-actions');
        if (!wrapper) return;
        const eventId = wrapper.dataset.eventId;
        const status = btn.dataset.status;

        try {
            console.log('RSVP click', {eventId, status});
            const res = await fetch(`/events/${eventId}/rsvp`, {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ status })
            });

            if (res.status === 401 || res.redirected) {
                const loginLink = document.querySelector('a[data-auth-open], a[href*="/login"], a[href*="auth/login"]');
                if (loginLink) loginLink.click(); else window.location = '/login';
                return;
            }

            if (!res.ok) {
                const text = await res.text().catch(()=>null);
                console.error('RSVP server error', res.status, text);
                throw new Error('Network error');
            }
            let data;
            try { data = await res.json(); } catch (e) {
                const text = await res.text().catch(()=>null);
                console.error('RSVP non-JSON response', text);
                throw e;
            }

            // Update counts (first span = interested, second = going)
            const counts = wrapper.querySelectorAll('.rsvp-count');
            if (counts && counts.length >= 2) {
                counts[0].textContent = `(${data.interested})`;
                counts[1].textContent = `(${data.going})`;
            } else {
                // fallback: update any rsvp-counts present
                wrapper.querySelectorAll('.rsvp-count').forEach((el, idx)=>{
                    el.textContent = `(${ idx === 0 ? data.interested : data.going })`;
                });
            }

            // Toggle active state
            wrapper.querySelectorAll('.rsvp-btn').forEach(b=>b.classList.remove('active'));
            btn.classList.add('active');

            // Update attendees HTML if provided
            const card = wrapper.closest('.event-card');
            if (data.attendees_html && card) {
                const newNode = document.createElement('div');
                newNode.innerHTML = data.attendees_html;
                const newAtt = newNode.firstElementChild;
                const oldAtt = card.querySelector('.attendees');
                if (newAtt && oldAtt) oldAtt.replaceWith(newAtt);
            }

            hhToastMsg('Updated: ' + data.status);
        } catch (err) {
            console.error('RSVP failed', err);
            hhToastMsg('Could not update RSVP');
        }
    }, false);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', __hh_init_rsvp);
} else {
    __hh_init_rsvp();
}
</script>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const chk = document.getElementById('has_end');
    const wrapper = document.getElementById('ends_wrapper');
    const starts = document.getElementById('starts_at');
    const ends = document.getElementById('ends_at');

    chk.addEventListener('change', function(){
        wrapper.classList.toggle('d-none', !chk.checked);
        if (chk.checked && starts.value && !ends.value) {
            // auto-fill ends to +3 hours
            const s = new Date(starts.value);
            s.setHours(s.getHours() + 3);
            // format to yyyy-mm-ddThh:mm
            const pad = (n)=>n.toString().padStart(2,'0');
            const v = `${s.getFullYear()}-${pad(s.getMonth()+1)}-${pad(s.getDate())}T${pad(s.getHours())}:${pad(s.getMinutes())}`;
            ends.value = v;
        }
    });

    // If starts changes while end visible, update end to +3 hours if end is empty
    starts.addEventListener('change', function(){
        if (chk.checked && !ends.value) {
            const s = new Date(starts.value);
            s.setHours(s.getHours() + 3);
            const pad = (n)=>n.toString().padStart(2,'0');
            const v = `${s.getFullYear()}-${pad(s.getMonth()+1)}-${pad(s.getDate())}T${pad(s.getHours())}:${pad(s.getMinutes())}`;
            ends.value = v;
        }
    });
});
</script>
@endpush

@push('styles')
    {{-- Safely include theme-events.css: prefer built asset from manifest, fall back to dev server URL if no manifest is present. --}}
    @php
        $__hh_manifest_path = public_path('build/manifest.json');
        if (file_exists($__hh_manifest_path)) {
            try {
                $__hh_manifest = json_decode(file_get_contents($__hh_manifest_path), true);

                // Direct key (normal Vite manifest key)
                if (isset($__hh_manifest['resources/css/theme-events.css']['file'])) {
                    $file = $__hh_manifest['resources/css/theme-events.css']['file'];
                    echo '<link rel="stylesheet" href="' . asset('build/' . $file) . '">';
                } else {
                    // Search manifest values for any compiled file that contains "theme" or "themes" (covers "themes.css" builds)
                    $found = false;
                    foreach ($__hh_manifest as $__hh_k => $__hh_v) {
                        if (is_array($__hh_v)) {
                            $file = $__hh_v['file'] ?? '';
                            $src = $__hh_v['src'] ?? $__hh_k;
                            $names = $__hh_v['names'] ?? [];

                            if (str_contains($file, 'theme') || str_contains($src, 'theme')) {
                                echo '<link rel="stylesheet" href="' . asset('build/' . $file) . '">';
                                $found = true;
                                break;
                            }

                            // also check names array for themes.css
                            foreach ($names as $n) {
                                if (str_contains($n, 'theme')) {
                                    echo '<link rel="stylesheet" href="' . asset('build/' . $file) . '">';
                                    $found = true;
                                    break 2;
                                }
                            }
                        }
                    }

                    // final fallback: known built filename (older builds)
                    if (!isset($found) || $found === false) {
                        if (file_exists(public_path('build/assets/themes-DkJXQ4bC.css'))) {
                            echo '<link rel="stylesheet" href="' . asset('build/assets/themes-DkJX4bC.css') . '">';
                        } elseif (file_exists(public_path('build/assets/themes-DkJX4bC.css'))) {
                            echo '<link rel="stylesheet" href="' . asset('build/assets/themes-DkJX4bC.css') . '">';
                        } elseif (file_exists(public_path('build/assets/themes-DkJX4bC.css'))) {
                            echo '<link rel="stylesheet" href="' . asset('build/assets/themes-DkJX4bC.css') . '">';
                        } elseif (file_exists(public_path('build/assets/themes-DkJX4bC.css'))) {
                            echo '<link rel="stylesheet" href="' . asset('build/assets/themes-DkJX4bC.css') . '">';
                        }
                    }
                }
            } catch (\Throwable $__hh_e) {
                // manifest unreadable - silently skip to avoid throwing in views
            }
        } else {
            // No build present: assume Vite dev server. Use VITE_SERVER_URL env if set, otherwise default to 127.0.0.1:5173
            $__hh_vite = env('VITE_SERVER_URL', 'http://127.0.0.1:5173');
            // Dev server exposes source files at /resources/... so link directly to the CSS file
            echo '<link rel="stylesheet" href="' . $__hh_vite . '/resources/css/theme-events.css">';

            // If the dev-server isn't running and you still want styles immediately, inline the local source as a fallback.
            // This ensures the events theme is applied even when the build/dev server isn't available.
            $__hh_local = resource_path('css/theme-events.css');
            if (file_exists($__hh_local)) {
                try {
                    $content = file_get_contents($__hh_local);
                    if (!empty(trim($content))) {
                        echo '<style>/* inlined theme-events.css */' . PHP_EOL . $content . '</style>';
                    }
                } catch (\Throwable $__hh_e) {
                    // ignore read errors
                }
            }
        }
    @endphp

    {{-- ALWAYS inline the local events theme as a last-resort, guaranteeing styles apply. --}}
    @php
        $__hh_local_inline = resource_path('css/theme-events.css');
        if (file_exists($__hh_local_inline)) {
            try {
                $__hh_inline_content = file_get_contents($__hh_local_inline);
                if (!empty(trim($__hh_inline_content))) {
                    echo '<style id="theme-events-inline">' . PHP_EOL . $__hh_inline_content . PHP_EOL . '</style>';
                }
            } catch (\Throwable $__hh_e) {
                // ignore
            }
        }
    @endphp
@endpush
