@extends('layouts.vendor')

@section('title','Attach Debug Uploads')

@section('content')
  <h1>Attach Debug Uploads</h1>
  <p>This is a simple non-JS form to attach files from storage/app/public/debug/uploads to your vendor profile.</p>
  <form method="POST" action="{{ route('vendor.store.attach_debug') }}">
    @csrf
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      @foreach($debugUploads as $d)
        <label style="display:block;width:220px;padding:6px;background:#fff;border-radius:6px;margin-bottom:8px">
          <input type="checkbox" name="paths[]" value="{{ $d }}"> {{ basename($d) }}
          <div style="margin-top:6px"><img src="{{ asset('storage/'.$d) }}" style="width:100%;height:120px;object-fit:cover"></div>
        </label>
      @endforeach
    </div>
    <div style="margin-top:12px"><button class="btn">Attach selected</button></div>
  </form>
@endsection
