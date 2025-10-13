{{-- resources/views/partials/auth-modal.blade.php --}}

<div id="auth-overlay" class="auth-overlay" hidden></div>
<div id="auth-modal" class="auth-modal" hidden aria-modal="true" role="dialog">
  <div class="modal-card">
    {{-- Close --}}
    <button type="button" class="close-btn" id="auth-close" aria-label="Close">×</button>

    {{-- Tabs --}}
    <div class="tabbar">
      <button class="tab active" data-tab="login">Login</button>
      <button class="tab" data-tab="register">Register</button>
    </div>

    {{-- Flash from controller (toast) --}}
    @if (session('auth_msg'))
      <div id="auth-flash"
           data-ok="{{ session('auth_ok') ? '1' : '0' }}"
           data-role="{{ session('auth_role') ?? '' }}"
           data-msg="{{ session('auth_msg') }}"
           data-redirect="{{ session('redirect') ?? '' }}"></div>
    @endif

    {{-- ONE errors payload so JS can reopen the correct tab --}}
    @if ($errors->any())
      <div id="auth-errors"
           data-form="{{ $errors->has('login') ? 'login' : (old('name') || old('password_confirmation') ? 'register' : 'login') }}"
           data-errors='@json($errors->all())'></div>
    @endif

    {{-- Scrollable area (so the long register form fits on small screens) --}}
    <div class="auth-content">

      {{-- LOGIN --}}
      <form id="login-form" class="form tabpane active" method="POST" action="{{ route('auth.login') }}">
        @csrf

        {{-- preserve redirect when opened from other pages (e.g., /cart -> /login?redirect=/checkout) --}}
        <input type="hidden" name="redirect" value="{{ $redirect ?? request('redirect') ?? '' }}" />

        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" />

        <label>Password</label>
        <input type="password" name="password" required autocomplete="current-password" />

        <label>Login as</label>
        <select name="login_as" required>
          <option value="user"   {{ old('login_as','user')==='user' ? 'selected' : '' }}>User</option>
          <option value="vendor" {{ old('login_as')==='vendor' ? 'selected' : '' }}>Vendor</option>
          <option value="admin"  {{ old('login_as')==='admin'  ? 'selected' : '' }}>Admin</option>
        </select>

        <label class="row">
          <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}> Remember me
        </label>

        <button class="btn primary" type="submit">Login</button>

        {{-- Inline login-only error --}}
        @if ($errors->has('login'))
          <div class="bg-red-100 text-red-700 px-3 py-2 rounded mt-2 text-sm">
            {{ $errors->first('login') }}
          </div>
        @endif
      </form>

      {{-- REGISTER --}}
<form id="register-form"
      class="form tabpane"
      method="POST"
      action="{{ route('auth.register') }}"
      enctype="multipart/form-data">
  @csrf

  {{-- Basic user fields --}}
  <label>Name</label>
  <input type="text"
         name="name"
         value="{{ old('name') }}"
         required
         maxlength="255"
         autocomplete="name" />

  <label>Email</label>
  <input type="email"
         name="email"
         value="{{ old('email') }}"
         required
         maxlength="255"
         autocomplete="email" />

  <label>Password</label>
  <input type="password"
         name="password"
         required
         minlength="8"
         pattern="(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}"
         title="At least 8 characters with at least one letter and one number"
         autocomplete="new-password" />

  <label>Confirm Password</label>
  <input type="password"
         name="password_confirmation"
         required
         minlength="8"
         autocomplete="new-password" />

  <label>Profile picture (optional)</label>
  <input type="file"
         name="avatar"
         accept="image/png,image/jpeg,image/webp" />

  {{-- Toggle: register as vendor --}}
  <label class="mt-3">
    <input type="checkbox"
           id="register_as_vendor"
           name="register_as_vendor"
           value="1"
           {{ old('register_as_vendor') ? 'checked' : '' }}>
    I want to be a vendor
  </label>

  {{-- Vendor extra fields (only visible when checked) --}}
  <div id="vendor-extra"
       class="vendor-extra"
       {{ old('register_as_vendor') ? '' : 'hidden' }}
       style="margin-top:1rem;">

    <div class="grid-2">
      <div>
        <label>Shop name</label>
        <input type="text"
               name="shop_name"
               value="{{ old('shop_name') }}"
               @if(old('register_as_vendor')) required @endif />
      </div>
      <div>
  <label>Vendor Category</label>
  <select name="vendor_category" @if(old('register_as_vendor')) required @endif>
    <option value="">Select category…</option>
    <option value="Handloom & Textiles" {{ old('vendor_category') == 'Handloom & Textiles' ? 'selected' : '' }}>Handloom & Textiles</option>
    <option value="Embroidery & Needlework" {{ old('vendor_category') == 'Embroidery & Needlework' ? 'selected' : '' }}>Embroidery & Needlework</option>
    <option value="Pottery & Terracotta" {{ old('vendor_category') == 'Pottery & Terracotta' ? 'selected' : '' }}>Pottery & Terracotta</option>
    <option value="Woodcraft & Bamboo" {{ old('vendor_category') == 'Woodcraft & Bamboo' ? 'selected' : '' }}>Woodcraft & Bamboo</option>
    <option value="Metal & Brassware" {{ old('vendor_category') == 'Metal & Brassware' ? 'selected' : '' }}>Metal & Brassware</option>
    <option value="Jewelry & Ornaments" {{ old('vendor_category') == 'Jewelry & Ornaments' ? 'selected' : '' }}>Jewelry & Ornaments</option>
    <option value="Painting & Folk Art" {{ old('vendor_category') == 'Painting & Folk Art' ? 'selected' : '' }}>Painting & Folk Art</option>
    <option value="Leather Craft" {{ old('vendor_category') == 'Leather Craft' ? 'selected' : '' }}>Leather Craft</option>
    <option value="Stone & Shell Craft" {{ old('vendor_category') == 'Stone & Shell Craft' ? 'selected' : '' }}>Stone & Shell Craft</option>
    <option value="Musical Instruments" {{ old('vendor_category') == 'Musical Instruments' ? 'selected' : '' }}>Musical Instruments</option>
    <option value="Food Heritage" {{ old('vendor_category') == 'Food Heritage' ? 'selected' : '' }}>Food Heritage</option>
    <option value="Folk Toys & Dolls" {{ old('vendor_category') == 'Folk Toys & Dolls' ? 'selected' : '' }}>Folk Toys & Dolls</option>
    <option value="Festive & Ritual Items" {{ old('vendor_category') == 'Festive & Ritual Items' ? 'selected' : '' }}>Festive & Ritual Items</option>
    <option value="Herbal & Natural Products" {{ old('vendor_category') == 'Herbal & Natural Products' ? 'selected' : '' }}>Herbal & Natural Products</option>
  </select>
</div>

      <div>
        <label>Description</label>
        <input type="text"
               name="description"
               value="{{ old('description') }}" />
      </div>
    </div>

    <label>Heritage Story</label>
    <textarea name="heritage_story" rows="3">{{ old('heritage_story') }}</textarea>

    <label>Address</label>
    <input type="text"
           name="address"
           value="{{ old('address') }}"
           maxlength="255" />

    <div class="grid-2">
      <div>
        <label>Phone</label>
        <input type="tel"
               name="phone"
               value="{{ old('phone') }}"
               pattern="[\d+\-\s()]{6,}"
               title="Enter a valid phone number (digits, +, -, () allowed)"
               @if(old('register_as_vendor')) required @endif />
      </div>

      <div>
        <label>District</label>
        <select name="district_id" @if(old('register_as_vendor')) required @endif>
          <option value="">Select district…</option>
          @foreach(($districts ?? []) as $d)
            <option value="{{ $d->id }}"
              {{ (string)old('district_id') === (string)$d->id ? 'selected' : '' }}>
              {{ $d->name }}
            </option>
          @endforeach
        </select>
      </div>
    </div>

    <label>Shop Logo (optional)</label>
    <input type="file"
           name="shop_logo"
           accept="image/png,image/jpeg,image/webp" />
  </div>

  <button class="btn primary" type="submit">Register</button>

  {{-- Errors (non-login) --}}
  @if ($errors->any() && !$errors->has('login'))
    <div class="bg-red-100 text-red-700 px-3 py-2 rounded mt-3">
      <ul class="list-disc list-inside text-sm">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif
</form>

{{-- Toggle script --}}
<script>
  (function () {
    const box = document.getElementById('register_as_vendor');
    const area = document.getElementById('vendor-extra');
    function toggle() {
      if (!box) return;
      if (box.checked) {
        area.removeAttribute('hidden');
      } else {
        area.setAttribute('hidden', 'hidden');
      }
    }
    if (box) {
      box.addEventListener('change', toggle);
      // run once on load as fallback
      toggle();
    }
  })();
</script>

    </div> {{-- /.auth-content --}}
  </div>   {{-- /.modal-card --}}
</div>     {{-- /#auth-modal --}}

{{-- Minimal modal CSS --}}
<style>
  [hidden]{display:none!important}
  body.modal-open{overflow:hidden}

  .auth-overlay{position:fixed;inset:0;background:rgba(30,18,8,.7);backdrop-filter:blur(4px);z-index:9998}
  .auth-modal{position:fixed;inset:0;display:grid;place-items:center;z-index:9999}
  .modal-card{
    background:#fff;width:min(92vw,560px);border-radius:18px;padding:12px 16px 16px;position:relative;
    box-shadow:0 8px 28px rgba(0,0,0,.25);display:flex;flex-direction:column;
    max-height:calc(100dvh - 32px);overflow:hidden;
  }
  .auth-content{overflow-y:auto;min-height:0;padding:8px 4px 4px}

  .close-btn{position:absolute;top:10px;right:12px;font-size:24px;background:none;border:none;cursor:pointer}
  .tabbar{display:flex;gap:8px;margin:6px 0 10px}
  .tab{flex:1;padding:10px;border-radius:10px;border:1px solid #ccc;cursor:pointer}
  .tab.active{background:#2f231d;color:#fff}

  .form{display:flex;flex-direction:column;gap:10px;margin-bottom:10px}
  input,select,textarea{padding:10px;border:1px solid #ccc;border-radius:10px}
  .btn.primary{padding:12px;border:0;border-radius:12px;background:#2f231d;color:#fff;font-weight:600;cursor:pointer}
  .vendor-extra{margin-top:10px;padding:10px;border:1px dashed #aaa;border-radius:10px}

  .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:10px}
  @media (max-width:640px){.grid-2{grid-template-columns:1fr}}

  .tabpane{display:none}
  .tabpane.active{display:flex;flex-direction:column}

  /* MAKE SURE modal is visible when opened */
  #auth-modal.is-open{display:grid!important;opacity:1!important;visibility:visible!important;z-index:2147483647!important}
  #auth-overlay.is-open{display:block!important;opacity:1!important;visibility:visible!important;z-index:2147483646!important}
</style>

{{-- Modal JS (open/close, tabs, vendor toggle, toasts, open on errors) --}}
<script>
(function(){
  const $  = (s, r=document)=>r.querySelector(s);
  const $$ = (s, r=document)=>Array.from(r.querySelectorAll(s));

  const overlay = $("#auth-overlay");
  const modal   = $("#auth-modal");
  const closeBtn= $("#auth-close");

  function openModal(tab='login'){
    if (!overlay || !modal) return;
    overlay.hidden = false; modal.hidden = false;
    overlay.classList.add('is-open'); modal.classList.add('is-open');
    document.body.classList.add('modal-open');
    setTab(tab);
  }
  function closeModal(){
    if (!overlay || !modal) return;
    overlay.classList.remove('is-open'); modal.classList.remove('is-open');
    overlay.hidden = true; modal.hidden = true;
    document.body.classList.remove('modal-open');
  }
  function setTab(name){
    $$(".tab").forEach(b=>b.classList.toggle('active', b.dataset.tab===name));
    $$(".tabpane").forEach(p=>p.classList.toggle('active', p.id.startsWith(name)));
  }

  // Tabs
  $$(".tab").forEach(b => b.addEventListener('click', () => setTab(b.dataset.tab)));

  // Close
  overlay?.addEventListener('click', closeModal);
  closeBtn?.addEventListener('click', closeModal);

  // Navbar triggers
  $$('[data-auth-open]').forEach(el=>{
    el.addEventListener('click', e=>{
      e.preventDefault();
      openModal(el.dataset.authOpen || 'login');
    });
  });

  // Vendor toggle + required fields
  const chk   = $("#register_as_vendor");
  const extra = $("#vendor-extra");
  function setVendorRequired(on){
    if(!extra) return;
    extra.hidden = !on;
    const req = ['shop_name','phone','district'];
    extra.querySelectorAll('input,textarea').forEach(el=>{
      if (req.includes(el.name)) on ? el.setAttribute('required','required') : el.removeAttribute('required');
    });
  }
  if (chk) {
    setVendorRequired(chk.checked);
    chk.addEventListener('change', ()=> setVendorRequired(chk.checked));
  }

  // Flash toast from controller
  const flash = $("#auth-flash");
  if (flash) {
    const ok  = flash.dataset.ok === '1';
    const msg = flash.dataset.msg || '';
    const red = flash.dataset.redirect || '';
    if (msg) {
      const t = document.createElement('div');
      t.className = 'toast ' + (ok ? 'ok' : 'err');
      t.textContent = msg + (flash.dataset.role ? ` (${flash.dataset.role})` : '');
      Object.assign(t.style,{position:'fixed',top:'20px',right:'20px',padding:'12px 14px',borderRadius:'10px',color:'#fff',zIndex:10000,background: ok ? '#0c7a43' : '#b42318'});
      document.body.appendChild(t);
      setTimeout(()=>t.remove(), 2500);
      if (ok && red) setTimeout(()=> window.location.href = red, 600);
    }
  }

  // Validation errors → open modal on correct tab + toast list
  const errs = $("#auth-errors");
  if (errs) {
    const which = errs.dataset.form || 'login';
    openModal(which);
    try {
      const list = JSON.parse(errs.dataset.errors || '[]');
      if (Array.isArray(list) && list.length) {
        const t = document.createElement('div');
        t.className = 'toast err';
        t.textContent = 'Please fix:\n• ' + list.join('\n• ');
        Object.assign(t.style,{position:'fixed',top:'20px',right:'20px',padding:'12px 14px',borderRadius:'10px',color:'#fff',zIndex:10000,whiteSpace:'pre-wrap',maxWidth:'420px',background:'#b42318'});
        document.body.appendChild(t);
        setTimeout(()=>t.remove(), 4500);
      }
    } catch (_) {}
  }

  // Client-side confirm password bubble
  const regForm = document.getElementById('register-form');
  if (regForm){
    const pwd  = regForm.querySelector('input[name="password"]');
    const conf = regForm.querySelector('input[name="password_confirmation"]');
    function sync(){
      if(conf && pwd){
        if(conf.value && conf.value !== pwd.value) conf.setCustomValidity('Passwords do not match');
        else conf.setCustomValidity('');
      }
    }
    pwd && pwd.addEventListener('input', sync);
    conf && conf.addEventListener('input', sync);
  }

  // for console testing
  window.__openAuth = openModal;
})();

// AJAX login handler: intercept login form submits and send XHR/Fetch expecting JSON
(function(){
  const loginForm = document.getElementById('login-form');
  if (!loginForm) return;

  loginForm.addEventListener('submit', async function(e){
    // always try AJAX; fallback to normal submit if fetch not supported
    if (!window.fetch) return;
    e.preventDefault();

    const form = e.target;
    const url = form.action;
    const formData = new FormData(form);

    // Pull CSRF token from meta tag (layout includes this)
    const meta = document.querySelector('meta[name="csrf-token"]');
    const csrf = meta ? meta.getAttribute('content') : null;

    try {
      const resp = await fetch(url, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrf || ''
        },
        body: formData,
        credentials: 'same-origin'
      });

      let json = null;
      try { json = await resp.json(); } catch(_) { json = null; }

      if (resp.ok && json && json.ok) {
        // success: show brief toast and redirect
        const t = document.createElement('div');
        t.className = 'toast ok';
        t.textContent = 'Login successful — redirecting…';
        Object.assign(t.style,{position:'fixed',top:'20px',right:'20px',padding:'12px 14px',borderRadius:'10px',color:'#fff',zIndex:10000,background:'#0c7a43'});
        document.body.appendChild(t);
        setTimeout(()=>t.remove(), 1800);

        const redirect = (json.redirect && json.redirect !== '') ? json.redirect : window.location.href;
        setTimeout(()=> window.location.href = redirect, 600);
        return;
      }

      // handle validation / auth errors
      const messages = [];
      if (json) {
        if (json.errors) {
          for (const k in json.errors) {
            if (Array.isArray(json.errors[k])) messages.push(...json.errors[k]);
            else messages.push(String(json.errors[k]));
          }
        } else if (json.message) {
          messages.push(String(json.message));
        }
      }
      if (messages.length === 0) messages.push('Login failed — please check your credentials.');

      const t = document.createElement('div');
      t.className = 'toast err';
      t.textContent = 'Login failed:\n• ' + messages.join('\n• ');
      Object.assign(t.style,{position:'fixed',top:'20px',right:'20px',padding:'12px 14px',borderRadius:'10px',color:'#fff',zIndex:10000,whiteSpace:'pre-wrap',maxWidth:'420px',background:'#b42318'});
      document.body.appendChild(t);
      setTimeout(()=>t.remove(), 4500);

    } catch (err) {
      const t = document.createElement('div');
      t.className = 'toast err';
      t.textContent = 'Network error while logging in';
      Object.assign(t.style,{position:'fixed',top:'20px',right:'20px',padding:'12px 14px',borderRadius:'10px',color:'#fff',zIndex:10000,background:'#b42318'});
      document.body.appendChild(t);
      setTimeout(()=>t.remove(), 2500);
    }
  });
})();
</script>
