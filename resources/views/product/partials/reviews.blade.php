@php
  // show approved reviews and pending ones (so users see their own immediately)
  $reviews = \App\Models\Review::where('product_id', $product->id)->whereIn('status',['approved','pending'])->latest()->take(20)->get();
  // compute average only from approved reviews
  $avg = round(\App\Models\Review::where('product_id', $product->id)->where('status','approved')->avg('rating') ?? 0, 1);
  $count = \App\Models\Review::where('product_id', $product->id)->where('status','approved')->count();
@endphp

<div class="reviews-section space-y-4">
  <div class="flex items-center justify-between">
    <h3 class="text-lg font-semibold" style="color:#5a3e2b">Reviews</h3>
      <div class="text-xs text-gray-400">{{ $count }} review{{ $count==1 ? '' : 's' }}</div>
  </div>

  @if($reviews->count())
    <div id="reviewsList" class="grid gap-4">
      @foreach($reviews as $rev)
        <div class="p-4 bg-white rounded-lg" style="box-shadow:0 12px 30px rgba(42,28,20,.06); border:1px solid rgba(90,62,43,.06);">
          <div class="flex items-start justify-between">
            <div>
              <div class="font-medium" style="color:#5a3e2b">{{ $rev->user->name ?? 'Customer' }}</div>
              <div class="text-xs text-gray-500">{{ $rev->created_at->format('M d, Y') }}</div>
            </div>
            <div class="flex items-center">
                  {{-- rating removed; showing text-only reviews --}}
            </div>
          </div>
          @if($rev->body)
            <div class="mt-3" style="color:#4a2f22">{{ $rev->body }}</div>
          @endif
          @if($rev->status === 'pending')
            <div class="mt-2"><span class="text-xs px-2 py-0.5 rounded-full" style="background:#fff7eb;color:#a16207;">In review</span></div>
          @endif
        </div>
      @endforeach
    </div>
  @else
    <div class="p-4 bg-white rounded-lg shadow-sm text-gray-600">No reviews yet. Be the first to review this product.</div>
  @endif

  @auth
    <div class="mt-4 bg-white p-4 rounded-lg shadow">
      <h4 class="mb-3 text-sm font-medium">Leave a review</h4>
      <form method="POST" action="{{ route('reviews.store') }}">@csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}" />

        {{-- rating input removed - text-only reviews --}}

        <label class="block mt-3">Comment
          <textarea name="body" class="block w-full border border-gray-200 p-2 rounded" rows="4">{{ old('body') }}</textarea>
        </label>

        @if($errors->any())
          <div class="mt-2 text-sm text-red-600">{{ $errors->first() }}</div>
        @endif

        <div class="mt-3"><button class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Submit review</button></div>
      </form>
    </div>
  @else
    <div class="p-3 bg-white rounded shadow text-sm text-gray-600">Please <a href="{{ route('login') }}" class="text-blue-600">login</a> to leave a review.</div>
  @endauth
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
  const form = document.querySelector('.reviews-section form');
  if (form) {
    form.addEventListener('submit', async function(e){
      e.preventDefault();
      const btn = form.querySelector('button[type=submit]') || form.querySelector('button');
      const formData = new FormData(form);
      btn.setAttribute('disabled','disabled');
      const url = form.getAttribute('action');
      // clear previous errors
      const errEl = form.querySelector('.review-error');
      if (errEl) errEl.remove();

      try {
  const resp = await fetch(url, { method: 'POST', body: formData, headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value } });
        if (resp.status === 422) {
          const data = await resp.json();
          const first = data.errors && Object.values(data.errors)[0] ? Object.values(data.errors)[0][0] : 'Validation error';
          const div = document.createElement('div'); div.className = 'review-error mt-2 text-sm text-red-600'; div.textContent = first; form.appendChild(div);
          btn.removeAttribute('disabled');
          return;
        }
        if (!resp.ok) throw new Error('Server error');
        const data = await resp.json();
        // success — prepend the new review as 'In review' so user sees it immediately
        if (data && data.review) {
          const rv = data.review;
          const list = document.getElementById('reviewsList');
          if (list) {
            const div = document.createElement('div');
            div.className = 'p-4 bg-white rounded-lg';
            div.style.boxShadow = '0 12px 30px rgba(42,28,20,.06)';
            div.style.border = '1px solid rgba(90,62,43,.06)';
            const user = (rv.user && rv.user.name) ? rv.user.name : 'You';
            const created = new Date().toLocaleDateString();
            // no stars - text-only review
            div.innerHTML = `
              <div class="flex items-start justify-between">
                <div>
                  <div class="font-medium" style="color:#5a3e2b">${user}</div>
                  <div class="text-xs text-gray-500">${created}</div>
                </div>
                <div class="flex items-center">${stars}</div>
              </div>
              <div class="mt-3" style="color:#4a2f22">${(rv.body||'')}</div>
              <div class="mt-2"><span class="text-xs px-2 py-0.5 rounded-full" style="background:#fff7eb;color:#a16207;">In review</span></div>
            `;
            list.prepend(div);
          }
            // update top count only
          if (data.count !== undefined) {
            const avgEl = document.querySelector('.reviews-section .text-xs.text-gray-400');
            if (avgEl) avgEl.textContent = `${data.count} review${data.count==1 ? '' : 's'}`;
          }
        }
        // clear input
        form.querySelector('textarea[name=body]').value = '';
        btn.removeAttribute('disabled');
      } catch(err){
        const div = document.createElement('div'); div.className = 'review-error mt-2 text-sm text-red-600'; div.textContent = err.message || 'Unable to submit review'; form.appendChild(div);
        btn.removeAttribute('disabled');
      }
    });
  }

  // star hover/fill behavior for rating inputs
  // no star hover/fill behavior - rating removed
});
</script>
@endsection
