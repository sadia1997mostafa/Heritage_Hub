@extends('layouts.app')
@section('title','Shop — Heritage Hub')

@section('content')
  <section class="parallax-hero">
    <div class="parallax-layer" style='background-image:url("{{ asset("images/heritage-map.jpg") }}")'></div>
    <div class="parallax-content">
      <div class="hh-container pad-section">
        <h2 class="section-title">Shop</h2>
        <p class="section-text">Discover artisan shops and crafted goods across Bangladesh.</p>
      </div>
    </div>
  </section>

  {{-- 3D rotating gallery (uses images you provide in public/images) --}}
  <section class="shop-rotator pad-section">
    <div class="hh-container">
      <div class="rotator-viewport">
        <div class="rotator" aria-hidden="false">
          <div class="rotor-slide"><img src="{{ asset('images/shop-rot-1.jpg') }}" alt="Crafts: textiles" loading="lazy"></div>
          <div class="rotor-slide"><img src="{{ asset('images/shop-rot-2.jpg') }}" alt="Crafts: pottery" loading="lazy"></div>
          <div class="rotor-slide"><img src="{{ asset('images/shop-rot-3.jpg') }}" alt="Weaving close-up" loading="lazy"></div>
          <div class="rotor-slide"><img src="{{ asset('images/shop-rot-4.jpg') }}" alt="Artisan at work" loading="lazy"></div>
        </div>
      </div>
      <div class="rotator-controls">
        <button class="rot-prev" aria-label="Previous">◀</button>
        <button class="rot-next" aria-label="Next">▶</button>
      </div>
    </div>
  </section>
  
  {{-- FEATURED CATEGORIES --}}
  @if(!empty($featuredCategories) && $featuredCategories->count())
  <section class="pad-section hh-container">
    <h2 class="section-title">Popular Categories</h2>
    <div class="chips">
      @foreach($featuredCategories as $cat)
        <a class="chip" href="{{ route('shop.category.show', $cat->slug) }}">{{ $cat->name }}</a>
      @endforeach
    </div>
  </section>
  @endif

  {{-- FEATURED PRODUCTS --}}
  @if(!empty($featuredProducts) && $featuredProducts->count())
  <section class="pad-section hh-container">
    <h2 class="section-title">Featured Crafts</h2>
    <div class="prod-grid">
      @foreach($featuredProducts as $p)
        @include('components.product-card', ['p' => $p])
      @endforeach
    </div>
    <div class="see-more">
      <a class="btn-ghost" href="{{ route('shop') }}">See more products</a>
    </div>
  </section>
  @endif

  {{-- FEATURED VENDORS --}}
  @if(!empty($featuredVendors) && $featuredVendors->count())
  <section class="pad-section hh-container">
    <h2 class="section-title">Featured Shops</h2>
    <div class="vendor-grid">
      @foreach($featuredVendors as $v)
        @include('components.vendor-card', ['v' => $v])
      @endforeach
    </div>
  </section>
  @endif

  {{-- Quick return request action --}}
  <section class="pad-section hh-container">
    <div class="bg-white p-4 rounded shadow">
      <h3 class="section-subtitle">Need to return an item?</h3>
      @auth
        @php $oi = request()->query('order_item_id'); @endphp
        @if($oi)
          <p class="mt-2">Submit a return request for Order Item #{{ $oi }} quickly.</p>
          <button id="openReturnModal" class="btn-primary mt-3">Request Return</button>
        @else
          <p class="mt-2">To request a return, open the order detail page and click "Request Return" next to the item, or use the full form.</p>
          <a href="{{ route('returns.create') }}" class="btn-primary mt-3 inline-block">Open Return Form</a>
        @endif
      @else
        <p class="mt-2">You need to be signed in to create a return request.</p>
        <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="btn-primary mt-3 inline-block">Sign in</a>
      @endauth
    </div>
  </section>

  {{-- Return modal (quick submit) --}}
  @auth
  <div id="returnModal" style="display:none;" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center">
    <div class="bg-white p-6 rounded w-96">
      <h3 class="font-semibold">Quick Return Request</h3>
      <form id="quickReturnForm" method="POST" action="{{ route('returns.store') }}" enctype="multipart/form-data" class="mt-3">
        @csrf
        <input type="hidden" name="order_item_id" value="{{ request()->query('order_item_id') }}">
        <label class="block text-sm">Reason</label>
        <textarea name="reason" class="block w-full border p-2 mt-1" required></textarea>
        <label class="block text-sm mt-2">Photos (optional)</label>
        <input type="file" name="photos[]" multiple accept="image/*" class="mt-1" />
        <div class="mt-3 flex justify-end gap-2">
          <button type="button" id="cancelReturn" class="px-3 py-1 border rounded">Cancel</button>
          <button type="submit" id="quickSubmitBtn" class="px-3 py-1 bg-green-600 text-white rounded">Submit</button>
        </div>
        <div id="quickReturnMsg" class="mt-2 text-sm"></div>
      </form>
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function(){
      const btn = document.getElementById('openReturnModal');
      const modal = document.getElementById('returnModal');
      const cancel = document.getElementById('cancelReturn');
      const form = document.getElementById('quickReturnForm');
      const msg = document.getElementById('quickReturnMsg');
      const submitBtn = document.getElementById('quickSubmitBtn');
      if(btn){ btn.addEventListener('click', ()=> modal.style.display='flex'); }
      if(cancel){ cancel.addEventListener('click', ()=> modal.style.display='none'); }

      if(form){
        form.addEventListener('submit', function(e){
          e.preventDefault();
          msg.textContent = '';
          submitBtn.disabled = true;
          const oi = form.querySelector('input[name="order_item_id"]').value;
          if(!oi){
            msg.textContent = 'Order item id is missing. Open the order detail page and click Request Return on the specific item, or use the full return form.';
            submitBtn.disabled = false;
            return;
          }
          const token = form.querySelector('input[name="_token"]');
          if(!token || !token.value){
            msg.textContent = 'Session may have expired. Please refresh the page and sign in again.';
            submitBtn.disabled = false;
            return;
          }

          // Build form data
          const fd = new FormData(form);

          fetch(form.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd,
            credentials: 'same-origin'
          }).then(r => r.json().then(j => ({status: r.status, body: j}))).then(resp => {
            if(resp.status >= 200 && resp.status < 300 && resp.body && resp.body.success){
              msg.textContent = resp.body.message || 'Return request submitted';
              msg.className = 'mt-2 text-green-700';
              modal.style.display = 'none';
            } else if(resp.body && resp.body.errors){
              const errs = resp.body.errors;
              const first = Object.values(errs)[0];
              msg.textContent = Array.isArray(first) ? first[0] : first;
              msg.className = 'mt-2 text-red-700';
            } else if(resp.status === 401){
              msg.textContent = 'Not signed in. Please refresh and sign in again.';
              msg.className = 'mt-2 text-red-700';
            } else {
              msg.textContent = resp.body && resp.body.message ? resp.body.message : 'An error occurred';
              msg.className = 'mt-2 text-red-700';
            }
          }).catch(err => {
            msg.textContent = 'Network error, please try again.';
            msg.className = 'mt-2 text-red-700';
          }).finally(()=> submitBtn.disabled = false);
        });
      }
    });
  </script>
  @endauth
@endsection
