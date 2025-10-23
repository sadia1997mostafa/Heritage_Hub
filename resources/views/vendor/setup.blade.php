@extends('layouts.vendor')

@section('title','Store Profile')

@section('content')
  <h1 class="text-2xl font-bold mb-4">Edit Store Profile</h1>
  <form method="POST" action="{{ route('vendor.store.setup.save') }}" enctype="multipart/form-data" class="space-y-4">
    @csrf
    <input type="hidden" name="gallery_count_debug" id="gallery_count_debug" value="0">
  <input type="hidden" name="uploaded_gallery_paths" id="uploaded_gallery_paths" value="">
    @if(session('gallery_debug'))
      <div style="background:#ffeeee;color:#6a0505;padding:8px;border-radius:8px;margin-bottom:8px">
        <strong>Debug: Gallery upload</strong>
        <pre style="white-space:pre-wrap">{{ json_encode(session('gallery_debug')) }}</pre>
      </div>
    @endif
    <label>Owner name</label>
    <input type="text" name="user_name" value="{{ old('user_name', $user->name ?? '') }}" placeholder="Owner name" class="form-control">
    <label>Owner email</label>
    <input type="email" name="user_email" value="{{ old('user_email', $user->email ?? '') }}" placeholder="Owner email" class="form-control">

    <label>Shop name</label>
    <input type="text" name="shop_name" value="{{ old('shop_name',$profile->shop_name) }}" placeholder="Shop name" class="form-control">

    <label>Support Email</label>
    <input type="email" name="support_email" value="{{ old('support_email',$profile->support_email) }}" placeholder="Support Email" class="form-control">

    <label>Support Phone</label>
    <input type="text" name="support_phone" value="{{ old('support_phone',$profile->support_phone) }}" placeholder="Support Phone" class="form-control">

    <label>Description</label>
    <textarea name="description" class="form-control">{{ old('description',$profile->description) }}</textarea>

    <label>Shop logo</label>
    <input type="file" name="shop_logo" class="form-control">

    <label>Banner</label>
    <input type="file" name="banner" class="form-control">

    <label>Gallery (upload multiple)</label>
  <input type="file" name="gallery[]" multiple accept="image/*" class="form-control">
  <div style="margin-top:6px">Selected files: <span id="gallery_count_visible">0</span></div>

    @if($profile->images->count())
      <div class="gallery-preview" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px">
        @foreach($profile->images as $img)
          <div style="position:relative;width:150px;border-radius:8px;overflow:hidden;background:#fff;padding:6px">
            <img src="{{ asset('storage/'.$img->path) }}" style="width:100%;height:100px;object-fit:cover;display:block" alt="gallery">
            <form method="POST" action="{{ route('vendor.store.gallery.remove',$img->id) }}" style="position:absolute;top:6px;right:6px">
              @csrf
              @method('DELETE')
              <button class="btn btn-sm" style="background:rgba(0,0,0,0.55);color:#fff;border-radius:6px;padding:4px 6px">Remove</button>
            </form>
          </div>
        @endforeach
      </div>
    @endif

    @if(!empty($debugUploads))
      {{-- quick attach moved outside main form (see below) --}}
    @endif

    <div style="margin-top:12px">
      <button class="btn btn-primary">Save</button>
    </div>
  </form>

  @if(!empty($debugUploads))
    <div style="margin-top:12px;padding:8px;border-radius:6px;background:#f4f4f4">
      <strong>Quick attach: files in debug/uploads</strong>
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px">
          @foreach($debugUploads as $d)
            <label style="display:block;width:200px;background:#fff;padding:6px;border-radius:6px">
              <input type="checkbox" class="debug-attach-checkbox" value="{{ $d }}"> {{ basename($d) }}
              <div style="margin-top:6px"><img src="{{ asset('storage/'.$d) }}" style="width:100%;height:80px;object-fit:cover"></div>
            </label>
          @endforeach
        </div>
        <div style="margin-top:8px">
          <button type="button" id="attach-debug-btn" class="btn" data-attach-url="{{ route('vendor.store.attach_debug') }}">Attach selected debug uploads</button>
          <span id="attach-debug-status" style="margin-left:8px"></span>
        </div>
    </div>
  @endif
@endsection

  <script>
// Small UX: confirm deletion
document.querySelectorAll('.gallery-preview form').forEach(f=>{
  f.addEventListener('submit', e=>{
    if (!confirm('Remove this image from gallery?')) e.preventDefault();
  });
});

// Debug: set hidden field with selected gallery files count so server can see if browser sends files
// This script is inline (not pushed) because the vendor layout doesn't include @stack('scripts').
(function(){
  const input = document.querySelector('input[name="gallery[]"]');
  const hidden = document.getElementById('gallery_count_debug');
  const visible = document.getElementById('gallery_count_visible');
  if (!input || !hidden) return;
  input.addEventListener('change', ()=>{
    hidden.value = input.files.length;
    if (visible) visible.textContent = input.files.length;
  });
})();

// If files are selected, upload them first to the debug endpoint and attach returned paths
(function(){
  const form = document.querySelector('form');
  if (!form) return;
  form.addEventListener('submit', async function(e){
    const fileInput = document.querySelector('input[name="gallery[]"]');
    const uploadedField = document.getElementById('uploaded_gallery_paths');
    if (!fileInput || !fileInput.files || fileInput.files.length === 0) return; // nothing to do

    // Prevent double-submit while we upload
    e.preventDefault();
    const fd = new FormData();
    // include CSRF token
    const token = document.querySelector('input[name="_token"]').value;
    fd.append('_token', token);
    for (let i=0;i<fileInput.files.length;i++) fd.append('gallery[]', fileInput.files[i]);

    try {
      const res = await fetch('/debug/vendor-upload', { method: 'POST', body: fd });
      const json = await res.json();
      if (!json.has_files || !json.files || json.files.length === 0) {
        // continue with normal submit to let server attempt native upload
        form.submit();
        return;
      }
      // collect paths and put into hidden input
      const paths = json.files.map(f=>f.path);
      uploadedField.value = JSON.stringify(paths);
      // clear file input to avoid re-sending large files
      try { fileInput.value = null; } catch(_) {}
      // submit original form (now with uploaded_gallery_paths)
      form.submit();
    } catch (err) {
      // on any error, fall back to normal submit
      form.submit();
    }
  });
})();

// Attach debug uploads via fetch to avoid nested forms
(function(){
  const btn = document.getElementById('attach-debug-btn');
  if (!btn) return;
  btn.addEventListener('click', async (e)=>{
    // prevent parent form submit
    try { e.preventDefault(); e.stopPropagation(); } catch(_){}
    btn.disabled = true;
    const boxes = Array.from(document.querySelectorAll('.debug-attach-checkbox'));
    const selected = boxes.filter(b=>b.checked).map(b=>b.value);
    const status = document.getElementById('attach-debug-status');
    if (selected.length === 0) { if (status) status.textContent = 'No files selected'; return; }
    if (status) { status.textContent = 'Attaching...'; }
    const fd = new FormData();
    selected.forEach(p=>fd.append('paths[]', p));
    // CSRF
    const tokenInput = document.querySelector('input[name="_token"]');
    if (tokenInput) fd.append('_token', tokenInput.value);
    try {
      const attachUrl = btn.getAttribute('data-attach-url');
      console.log('Attach debug: posting to', attachUrl, selected);
      const res = await fetch(attachUrl, { method: 'POST', body: fd, credentials: 'same-origin' });
      if (!res.ok) throw new Error('Network response not ok');
      // on success, reload so images appear
      if (status) status.textContent = 'Attached. Reloading...';
      setTimeout(()=>location.reload(), 700);
    } catch (e) {
      console.error('Attach debug failed', e);
      if (status) status.textContent = 'Failed to attach: '+e.message;
      btn.disabled = false;
    }
  });
})();
</script>
