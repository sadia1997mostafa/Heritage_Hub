@extends('layouts.app')

@section('title','Edit Vlog')

@section('content')
<style>
/* Inline vlog styles (minimal) */
.vlog-container{ padding:30px 0; max-width:1120px; margin-inline:auto; }
.vlog-form{ margin:18px 0; padding:14px; border-radius:12px; background:#fff; border:1px solid rgba(62,39,35,.06); }
.form-control{ width:100%; padding:10px; border-radius:10px; border:1px solid rgba(62,39,35,.12); }
.accent{ background: var(--hh-secondary, #c58940); border:1px solid var(--hh-secondary, #c58940); color:#2b1c11; padding:8px 14px; border-radius:10px; font-weight:800; }
</style>

<div class="vlog-container">
  <h1>Edit Vlog</h1>

  <form method="POST" action="{{ route('vlogs.update', $vlog) }}" enctype="multipart/form-data" class="vlog-form">
    @csrf
    <div class="mb-2">
      <input name="title" value="{{ old('title',$vlog->title) }}" placeholder="Short title (optional)" class="form-control" />
    </div>
    <div class="mb-2">
      <textarea name="body" rows="6" class="form-control">{{ old('body',$vlog->body) }}</textarea>
    </div>
    <div class="mb-2">
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
