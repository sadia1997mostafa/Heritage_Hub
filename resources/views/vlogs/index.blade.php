@extends('layouts.app')

@section('title','Heritage Timeline')

@section('content')
<div class="hh-container" style="padding:30px 0">
  <h1>Heritage Timeline</h1>
  <p>Share short vlogs about places, craft, and memory. Be respectful.</p>

  @auth
  <div style="margin:18px 0;padding:12px;border:1px solid #eee;border-radius:8px">
    <form method="POST" action="{{ route('vlogs.store') }}" enctype="multipart/form-data">
      @csrf
      <div style="margin-bottom:8px">
        <input name="title" placeholder="Short title (optional)" style="width:100%;padding:8px" />
      </div>
      <div style="margin-bottom:8px">
        <textarea name="body" rows="4" placeholder="Write your vlog" style="width:100%;padding:8px"></textarea>
      </div>
      <div style="margin-bottom:8px">
        <label>Attach images</label>
        <input type="file" name="images[]" multiple accept="image/*" />
      </div>
      <div style="text-align:right">
        <button class="accent">Post Vlog</button>
      </div>
    </form>
  </div>
  @else
  <p><a href="{{ route('login') }}?redirect={{ route('vlogs.index') }}">Log in</a> to post a vlog.</p>
  @endauth

  <div style="margin-top:18px;display:grid;grid-template-columns:1fr 380px;gap:20px">
    <div>
      <h2>My Timeline</h2>
      @if($myVlogs)
        @foreach($myVlogs as $v)
          <article style="padding:14px;border-bottom:1px solid #f0f0f0">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:6px">
              <div style="width:44px;height:44px;border-radius:6px;background:#ddd;display:flex;align-items:center;justify-content:center">{{ strtoupper(substr($v->user->name,0,1)) }}</div>
              <div>
                <strong>{{ $v->user->name }}</strong>
                <div style="color:#666;font-size:13px">{{ $v->published_at ? $v->published_at->diffForHumans() : $v->created_at->diffForHumans() }}</div>
              </div>
            </div>
            @if($v->title)<h3 style="margin:6px 0">{{ $v->title }}</h3>@endif
            <div style="white-space:pre-wrap">{!! $v->body_html !!}</div>

            @if($v->images && $v->images->count())
              <div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap">
                @foreach($v->images as $img)
                  <div style="width:160px;max-height:120px;overflow:hidden;border:1px solid #eee;padding:4px">
                    <img src="{{ asset('storage/' . $img->path) }}" style="width:100%;height:auto;display:block" alt="vlog image">
                  </div>
                @endforeach
              </div>
            @endif

            <div style="margin-top:8px">
              <strong>Status:</strong>
              @if($v->approved)
                <span style="color:green">Approved</span>
              @else
                <span style="color:orange">Waiting for approval</span>
              @endif
            </div>

            <div style="margin-top:8px">
              <a href="{{ route('vlogs.edit', $v) }}">Edit</a>
              <form method="POST" action="{{ route('vlogs.destroy', $v) }}" style="display:inline">@csrf @method('DELETE')<button>Delete</button></form>
            </div>
          </article>
        @endforeach

        <div style="margin-top:12px">{{ $myVlogs->links('pagination::bootstrap-4') }}</div>
      @else
        <p>Log in to see and manage your vlogs.</p>
      @endif
    </div>

    <aside>
      <h2>Story Tales</h2>
      @foreach($storyVlogs as $v)
        <article style="padding:10px;border-bottom:1px solid #f3f3f3">
          <strong>{{ $v->user->name }}</strong>
          <div style="color:#666;font-size:13px">{{ $v->published_at ? $v->published_at->diffForHumans() : $v->created_at->diffForHumans() }}</div>
          <div style="margin-top:6px;white-space:pre-wrap">{!! $v->body_html !!}</div>
          @if($v->images->count())
            <div style="display:flex;gap:6px;margin-top:6px">
              @foreach($v->images as $img)
                <img src="{{ asset('storage/' . $img->path) }}" style="width:80px;height:auto;border:1px solid #eee;padding:3px">
              @endforeach
            </div>
          @endif
        </article>
      @endforeach

      <div style="margin-top:12px">{{ $storyVlogs->links('pagination::bootstrap-4') }}</div>
    </aside>
  </div>
</div>
@endsection
