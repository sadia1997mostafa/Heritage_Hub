@extends('layouts.app')

@section('title','Heritage Timeline')

@section('content')
<style>
/* Inline vlog styles — small subset copied from theme-events.css to guarantee appearance */
.vlog-container{ padding:30px 0; max-width:1120px; margin-inline:auto; }
.vlog-top{ display:flex; gap:10px; align-items:center; margin-bottom:12px; }
.vlog-form{ margin:18px 0; padding:14px; border-radius:12px; background: linear-gradient(180deg,#FFF9F0,#FFF3E6); border:1px solid rgba(62,39,35,.06); }
.vlog-form .form-control{ width:100%; padding:10px; border-radius:10px; border:1px solid rgba(62,39,35,.12); }
.vlog-form .accent{ background: var(--hh-secondary); border:1px solid var(--hh-secondary); color:#2b1c11; padding:8px 14px; border-radius:10px; font-weight:800; }
.vlog-grid{ display:grid; grid-template-columns: 1fr 360px; gap:20px; margin-top:18px; }
.vlog-article{ padding:14px; border-radius:10px; background: linear-gradient(180deg,#FFF9F0,#FFF3E6); border:1px solid rgba(62,39,35,.04); box-shadow: 0 8px 20px rgba(10,8,6,0.04); margin-bottom:10px; }
.vlog-head{ display:flex; gap:12px; align-items:center; margin-bottom:8px; }
.vlog-avatar{ width:44px; height:44px; border-radius:8px; background:#eee; display:flex; align-items:center; justify-content:center; font-weight:800; color:#6b4434; }
.vlog-title{ margin:6px 0; font-size:18px; color:var(--hh-brown-dark, #6b4434); font-weight:700; }
.vlog-meta{ color:#6b6b6b; font-size:13px; }
.vlog-body{ white-space:pre-wrap; color:#3b2a24; line-height:1.6; margin-top:6px; }
.vlog-images{ display:flex; gap:12px; margin-top:10px; flex-wrap:wrap; }
.vlog-img-thumb{ width:200px; height:150px; overflow:hidden; border:1px solid rgba(62,39,35,.06); border-radius:8px; }
.vlog-img-thumb img{ width:100%; height:100%; object-fit:cover; display:block; }
.vlog-actions{ display:flex; gap:10px; align-items:center; margin-top:10px; }
/* button styles for vlog actions and tabs */
.vlog-actions a, .vlog-actions button{ font-weight:800; text-decoration:none; border-radius:10px; padding:8px 12px; display:inline-flex; align-items:center; gap:8px; cursor:pointer; transition:transform .12s ease, box-shadow .12s ease, opacity .12s ease; border:1px solid transparent; }
.vlog-actions a.btn-primary, .vlog-actions button.btn-primary{ background:linear-gradient(180deg,var(--brand),var(--brand-600)); color:#fff; border:1px solid rgba(0,0,0,.06); box-shadow:0 10px 30px rgba(42,28,20,.08); }
.vlog-actions a.btn-outline-secondary, .vlog-actions button.btn-outline-secondary{ background:transparent; border:1px solid rgba(107,78,61,.12); color:var(--brand-600); }
.vlog-actions a:hover, .vlog-actions button:hover{ transform:translateY(-3px); opacity:0.98 }
.vlog-actions a:active, .vlog-actions button:active{ transform:translateY(0) scale(.995); }

/* small utility button classes used by the tabs */
.vlog-top .btn{ padding:8px 12px; border-radius:12px; font-weight:800; text-decoration:none; display:inline-flex; align-items:center; gap:8px; position:relative; transform-style:preserve-3d; transition: transform .18s cubic-bezier(.2,.9,.2,1), box-shadow .18s ease, opacity .12s ease; }
.btn-sm{ padding:6px 10px; font-size:.92rem; border-radius:10px; }
.btn-primary{ background:linear-gradient(180deg,#FFD97A,#D9A441); color:#2b1c11; border:0; box-shadow: 0 10px 28px rgba(42,28,20,.12), inset 0 -3px 0 rgba(0,0,0,.06); transform:translateZ(0); }
.btn-outline-secondary{ background:linear-gradient(180deg,#fff,#fff8ef); border:1px solid rgba(42,28,20,.08); color:var(--brand-600); box-shadow: 0 8px 20px rgba(42,28,20,.06); }
.vlog-top .btn:hover{ transform:translateY(-6px) rotateX(2deg); box-shadow: 0 22px 42px rgba(42,28,20,.18); opacity:0.99 }
.vlog-top .btn:active{ transform:translateY(0) scale(.995); box-shadow: 0 8px 18px rgba(42,28,20,.10); }

/* accent / post button improved 3D */
.vlog-form .accent{ background:linear-gradient(180deg,#FFD97A,#D9A441); border:0; color:#2b1c11; padding:10px 16px; border-radius:12px; font-weight:800; box-shadow: 0 14px 36px rgba(42,28,20,.10), inset 0 -3px 0 rgba(0,0,0,.04); transition:transform .12s ease, box-shadow .12s ease; }
.vlog-form .accent:hover{ transform:translateY(-5px); box-shadow: 0 24px 48px rgba(42,28,20,.16); }
.vlog-form .accent:active{ transform:translateY(0) scale(.995); }
.vlog-sidebar{ background: linear-gradient(180deg, rgba(250,243,224,0.7), rgba(245,236,216,0.6)); padding:12px; border-radius:12px; border:1px solid rgba(62,39,35,.04); }
.vlog-sidebar h2{ font-size:18px; margin-bottom:8px; color:var(--hh-brown-dark, #6b4434); }
.vlog-sidebar article{ padding:10px; border-bottom:1px solid rgba(62,39,35,.03); background: transparent; }
.vlog-sidebar .excerpt{ color:#6b6b6b; font-size:13px; margin-top:6px; }
@media (max-width:900px){ .vlog-grid{ grid-template-columns: 1fr; } .vlog-sidebar{ order:2; } }
</style>

<div class="vlog-container">
  <h1>Heritage Timeline</h1>
  <p>Share short vlogs about places, craft, and memory. Be respectful.</p>

  {{-- tabs moved below the form so they appear under the post box as requested --}}

  @auth
  <div class="vlog-form">
    <form method="POST" action="{{ route('vlogs.store') }}" enctype="multipart/form-data">
      @csrf
      <div class="mb-2">
        <input name="title" placeholder="Short title (optional)" class="form-control" />
      </div>
      <div class="mb-2">
        <textarea name="body" rows="4" placeholder="Write your vlog" class="form-control"></textarea>
      </div>
      <div class="mb-2">
        <label>Attach images</label>
        <input type="file" name="images[]" multiple accept="image/*" />
      </div>
      <div style="text-align:right">
        <button type="submit" class="accent">Post Vlog</button>
      </div>
    </form>
  </div>
  @else
    <p><a href="{{ route('login') }}?redirect={{ route('vlogs.index') }}" class="btn btn-sm btn-primary">Log in</a> to post a vlog.</p>
  @endauth

  {{-- small inner tabs under the form/login area --}}
  <div class="vlog-top" style="margin-bottom:8px">
    <a href="{{ route('vlogs.index', ['tab'=>'stories']) }}" class="btn btn-sm {{ ($tab ?? 'stories') === 'stories' ? 'btn-primary' : 'btn-outline-secondary' }}">Story Tales</a>
    <a href="{{ route('vlogs.index', ['tab'=>'mine']) }}" class="btn btn-sm {{ ($tab ?? '') === 'mine' ? 'btn-primary' : 'btn-outline-secondary' }}">My Timeline</a>
  </div>

  <div class="vlog-grid">
    <div>
      @if(($tab ?? 'stories') === 'mine')
        <h2>My Timeline</h2>
        @if($myVlogs)
          @foreach($myVlogs as $v)
            <article class="vlog-article">
              <div class="vlog-head">
                <div class="vlog-avatar">{{ strtoupper(substr($v->user->name,0,1)) }}</div>
                <div>
                  <strong>{{ $v->user->name }}</strong>
                  <div class="vlog-meta">{{ $v->published_at ? $v->published_at->diffForHumans() : $v->created_at->diffForHumans() }}</div>
                </div>
              </div>
              @if($v->title)<div class="vlog-title">{{ $v->title }}</div>@endif
              <div class="vlog-body">{!! $v->body_html !!}</div>

              @if($v->images && $v->images->count())
                <div class="vlog-images">
                  @foreach($v->images as $img)
                    <div class="vlog-img-thumb">
                      <img src="{{ asset('storage/' . $img->path) }}" alt="vlog image">
                    </div>
                  @endforeach
                </div>
              @endif

              <div class="vlog-actions">
                <div><strong>Status:</strong> @if($v->approved)<span style="color:green">Approved</span>@else<span style="color:orange">Waiting for approval</span>@endif</div>
                <div style="margin-left:auto"> <a href="{{ route('vlogs.edit', $v) }}" class="btn btn-sm btn-outline-secondary">Edit</a> <form method="POST" action="{{ route('vlogs.destroy', $v) }}" style="display:inline">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-secondary">Delete</button></form></div>
              </div>
            </article>
          @endforeach

          <div style="margin-top:12px">{{ $myVlogs->links('pagination::bootstrap-4') }}</div>
        @else
          <p>Log in to see and manage your vlogs.</p>
        @endif
      @else
        <h2>Story Tales</h2>
        @foreach($storyVlogs as $v)
          <article class="vlog-article">
            <div class="vlog-head">
              <div class="vlog-avatar">{{ strtoupper(substr($v->user->name,0,1)) }}</div>
              <div>
                <strong>{{ $v->user->name }}</strong>
                <div class="vlog-meta">{{ $v->published_at ? $v->published_at->diffForHumans() : $v->created_at->diffForHumans() }}</div>
              </div>
            </div>
            <div class="vlog-body">{!! $v->body_html !!}</div>
            @if($v->images->count())
              <div class="vlog-images">
                @foreach($v->images as $img)
                  <div class="vlog-img-thumb">
                    <img src="{{ asset('storage/' . $img->path) }}" alt="vlog image">
                  </div>
                @endforeach
              </div>
            @endif
          </article>
        @endforeach

        <div style="margin-top:12px">{{ $storyVlogs->links('pagination::bootstrap-4') }}</div>
      @endif
    </div>

    <aside class="vlog-sidebar">
      {{-- Sidebar content duplicated for both tabs; keep storylist here for context when viewing mine as well --}}
      <h2>Quick Story Feed</h2>
      @foreach($storyVlogs->take(6) as $v)
        <article>
          <strong>{{ $v->user->name }}</strong>
          <div class="vlog-meta">{{ $v->published_at ? $v->published_at->diffForHumans() : $v->created_at->diffForHumans() }}</div>
          <div class="excerpt">{!! Str::limit(strip_tags($v->body_html),120) !!}</div>
        </article>
      @endforeach

      <div style="margin-top:12px"><a href="{{ route('vlogs.index', ['tab'=>'stories']) }}">See all story tales</a></div>
    </aside>
  </div>
</div>
@endsection
