@extends('layouts.app')

@section('title','Review Vlogs')

@section('content')
<div class="hh-container" style="padding:30px 0">
  <h1>Vlogs Review</h1>

  @foreach($vlogs as $v)
    <div style="padding:12px;border:1px solid #eee;margin-bottom:8px">
      <div style="display:flex;align-items:center;gap:12px">
        <div style="width:44px;height:44px;border-radius:6px;background:#ddd;display:flex;align-items:center;justify-content:center">{{ strtoupper(substr($v->user->name,0,1)) }}</div>
        <div>
          <strong>{{ $v->user->name }}</strong>
          <div style="color:#666;font-size:13px">{{ $v->created_at->diffForHumans() }}</div>
        </div>
      </div>
      <div style="margin-top:8px">{!! $v->body_html !!}</div>
      @if($v->images->count())
        <div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap">
          @foreach($v->images as $img)
            <img src="{{ asset('storage/' . $img->path) }}" style="width:120px;height:auto;display:block;border:1px solid #eee;padding:4px">
          @endforeach
        </div>
      @endif
      <div style="margin-top:8px">
        @if(!$v->approved)
          <form method="POST" action="{{ route('admin.vlogs.approve', $v) }}" style="display:inline">@csrf<button class="accent">Approve</button></form>
        @else
          <span style="color:green">Approved</span>
        @endif
      </div>
    </div>
  @endforeach

  <div style="margin-top:12px">{{ $vlogs->links() }}</div>
</div>
@endsection
