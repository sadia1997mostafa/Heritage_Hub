@extends('layouts.app')

@section('title','Edit Vlog')

@section('content')
<div class="hh-container" style="padding:30px 0">
  <h1>Edit Vlog</h1>

  <form method="POST" action="{{ route('vlogs.update', $vlog) }}" enctype="multipart/form-data">
    @csrf
    <div style="margin-bottom:8px">
      <input name="title" value="{{ old('title',$vlog->title) }}" placeholder="Short title (optional)" style="width:100%;padding:8px" />
    </div>
    <div style="margin-bottom:8px">
      <textarea name="body" rows="6" style="width:100%;padding:8px">{{ old('body',$vlog->body) }}</textarea>
    </div>
    <div style="margin-bottom:8px">
      <label>Attach images (optional)</label>
      <input type="file" name="images[]" multiple accept="image/*">
    </div>
    <div style="text-align:right">
      <button class="accent">Save</button>
    </div>
  </form>

  @if($vlog->images->count())
    <h3>Images</h3>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      @foreach($vlog->images as $img)
        <div style="width:160px;">
          <img src="{{ asset('storage/' . $img->path) }}" style="width:100%;height:auto;display:block">
        </div>
      @endforeach
    </div>
  @endif
</div>
@endsection
