<div id="auth-overlay" class="auth-overlay" hidden></div>
<div id="auth-modal" class="auth-modal" hidden aria-modal="true" role="dialog">
  <div class="modal-card">
    <button class="close-btn" id="auth-close" aria-label="Close">×</button>

    <!-- Tabs -->
    <div class="tabbar">
      <button class="tab active" data-tab="login">Login</button>
      <button class="tab" data-tab="register">Register</button>
    </div>

    <!-- Flash Message -->
    @if (session('auth_msg'))
      <div id="auth-flash"
           data-ok="{{ session('auth_ok') ? '1' : '0' }}"
           data-role="{{ session('auth_role') ?? '' }}"
           data-msg="{{ session('auth_msg') }}"
           data-redirect="{{ session('redirect') ?? '' }}"></div>
           {{-- Validation errors payload (so we can reopen the right tab) --}}
@if ($errors->any())
  <div id="auth-errors"
       data-form="{{ old('name') || old('password_confirmation') ? 'register' : 'login' }}"
       data-errors='@json($errors->all())'></div>
@endif

    @endif

  <!-- Login Form -->
<form id="login-form" class="form tabpane active" method="POST" action="{{ route('auth.login') }}">
  @csrf

  <label>Email</label>
  <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" />

  <label>Password</label>
  <input type="password" name="password" required autocomplete="current-password" />

  <label>Login as</label>
  <select name="login_as" required>
    <option value="user" {{ old('login_as','user')==='user' ? 'selected' : '' }}>User</option>
    <option value="vendor" {{ old('login_as')==='vendor' ? 'selected' : '' }}>Vendor</option>
    <option value="admin" {{ old('login_as')==='admin' ? 'selected' : '' }}>Admin</option>
  </select>

  <label class="row">
    <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}> Remember me
  </label>

  <button class="btn primary" type="submit">Login</button>
  @if ($errors->has('login'))
  <div class="bg-red-100 text-red-700 px-3 py-2 rounded mt-2 text-sm">
      {{ $errors->first('login') }}
  </div>
@endif
</form>

<!-- {{-- Login errors (optional – shows server-side validation/auth errors) --}}
@error('login') 
  <div class="bg-red-100 text-red-700 px-3 py-2 rounded mb-3 text-sm">{{ $message }}</div>
@enderror -->


{{-- Tell JS there are errors and which tab to open --}}
@if ($errors->any())
  <div id="auth-errors"
       data-form="{{ $errors->has('login') ? 'login' : 'register' }}"
       data-errors='@json($errors->all())'></div>
@endif

<!-- Register Form -->
<form id="register-form" class="form tabpane" method="POST" action="{{ route('auth.register') }}" enctype="multipart/form-data">
  @csrf

  <label>Name</label>
  <input type="text" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name" />

  <label>Email</label>
  <input type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" />

  <label>Password</label>
  <input
    type="password"
    name="password"
    required
    minlength="8"
    pattern="(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}"
    title="At least 8 characters with at least one letter and one number"
    autocomplete="new-password"
  />

  <label>Confirm Password</label>
  <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" />

  <label>Profile picture (optional)</label>
  <input type="file" name="avatar" accept="image/png,image/jpeg,image/webp" />

  <label>
    <input type="checkbox" id="register_as_vendor" name="register_as_vendor" value="1" {{ old('register_as_vendor') ? 'checked' : '' }}>
    I want to be a vendor
  </label>

  <div id="vendor-extra" class="vendor-extra" {{ old('register_as_vendor') ? '' : 'hidden' }}>
    <div class="grid-2">
      <div>
        <label>Shop name</label>
        <input type="text" name="shop_name" value="{{ old('shop_name') }}" />
      </div>
      <div>
        <label>Description</label>
        <input type="text" name="description" value="{{ old('description') }}" />
      </div>
    </div>

    <label>Heritage Story</label>
    <textarea name="heritage_story" rows="3">{{ old('heritage_story') }}</textarea>

    <label>Address</label>
    <input type="text" name="address" value="{{ old('address') }}" maxlength="255" />

    <div class="grid-2">
      <div>
        <label>Phone</label>
        <input
          type="tel"
          name="phone"
          value="{{ old('phone') }}"
          pattern="[\d+\-\s()]{6,}"
          title="Enter a valid phone number (digits, +, -, () allowed)"
        />
      </div>
      <div>
        <label>District</label>
        <input type="text" name="district" value="{{ old('district') }}" maxlength="100" />
      </div>
    </div>

    <label>Shop Logo (optional)</label>
    <input type="file" name="shop_logo" accept="image/png,image/jpeg,image/webp" />
  </div>

  <button class="btn primary" type="submit">Register</button>
</form>

{{-- Register errors (shows all validation errors) --}}
@if ($errors->any())
  <div class="bg-red-100 text-red-700 px-3 py-2 rounded mt-3">
    <ul class="list-disc list-inside text-sm">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

{{-- helper CSS for two-column fields (optional) --}}
<style>
  .grid-2 { display:grid; grid-template-columns: 1fr 1fr; gap:10px; }
  @media (max-width: 640px){ .grid-2 { grid-template-columns: 1fr; } }
</style>

{{-- tiny JS to toggle vendor required fields + confirm password bubble --}}
<script>
(function(){
  const regForm = document.getElementById('register-form');
  const vendorChk = document.getElementById('register_as_vendor');
  const vendorBox = document.getElementById('vendor-extra');

  function setVendorRequired(on){
    if(!vendorBox) return;
    vendorBox.hidden = !on;
    const reqNames = ['shop_name','phone','district'];
    vendorBox.querySelectorAll('input,textarea').forEach(el=>{
      if(reqNames.includes(el.name)){
        if(on) el.setAttribute('required','required');
        else el.removeAttribute('required');
      }
    });
  }
  if(vendorChk){
    setVendorRequired(vendorChk.checked);
    vendorChk.addEventListener('change', ()=> setVendorRequired(vendorChk.checked));
  }

  // client-side confirm password validity (native bubble)
  if(regForm){
    const pwd  = regForm.querySelector('input[name="password"]');
    const conf = regForm.querySelector('input[name="password_confirmation"]');
    function sync(){
      if(conf && pwd){
        if(conf.value && conf.value !== pwd.value){
          conf.setCustomValidity('Passwords do not match');
        } else {
          conf.setCustomValidity('');
        }
      }
    }
    pwd && pwd.addEventListener('input', sync);
    conf && conf.addEventListener('input', sync);
  }
})();
</script>

<style>
    /* make sure the boolean [hidden] attribute always hides elements */
[hidden] { display: none !important; }
.auth-overlay {position:fixed;inset:0;background:rgba(30,18,8,.7);backdrop-filter:blur(4px);z-index:9998}
.auth-modal {position:fixed;inset:0;display:grid;place-items:center;z-index:9999}
.modal-card {background:#fff;max-width:460px;width:92%;border-radius:18px;padding:22px;
  box-shadow:0 8px 28px rgba(0,0,0,.25);position:relative}
.close-btn {position:absolute;top:10px;right:12px;font-size:24px;background:none;border:none;cursor:pointer}
.tabbar {display:flex;gap:8px;margin-bottom:12px}
.tab {flex:1;padding:10px;border-radius:10px;border:1px solid #ccc;cursor:pointer}
.tab.active {background:#2f231d;color:#fff}
.form {display:flex;flex-direction:column;gap:10px}
input,select,textarea {padding:10px;border:1px solid #ccc;border-radius:10px}
.btn.primary {padding:12px;border:0;border-radius:12px;background:#2f231d;color:#fff;font-weight:600;cursor:pointer}
.vendor-extra {margin-top:10px;padding:10px;border:1px dashed #aaa;border-radius:10px}
.toast {position:fixed;top:20px;right:20px;padding:12px 14px;border-radius:10px;color:#fff;z-index:10000}
.toast.ok {background:#0c7a43}
.toast.err {background:#b42318}
.tabpane {display:none}
.tabpane.active {display:flex;flex-direction:column}
</style>

<script>
(function(){
  const $  = (s, r=document)=>r.querySelector(s);
  const $$ = (s, r=document)=>Array.from(r.querySelectorAll(s));

  const overlay = $("#auth-overlay");
  const modal   = $("#auth-modal");
  const closeBtn= $("#auth-close");

  function openModal(tab='login'){
    if (!overlay || !modal) return;
    overlay.hidden = false;
    modal.hidden   = false;
    setTab(tab);
  }
  function closeModal(){
    if (!overlay || !modal) return;
    overlay.hidden = true;
    modal.hidden   = true;
  }
  function setTab(name){
    $$(".tab").forEach(b=>b.classList.toggle('active', b.dataset.tab===name));
    $$(".tabpane").forEach(p=>p.classList.toggle('active', p.id.startsWith(name)));
  }

  // Tabs
  $$(".tab").forEach(b => b.addEventListener('click', () => setTab(b.dataset.tab)));

  // Vendor expand
  const chk = $("#register_as_vendor");
  const extra = $("#vendor-extra");
  if (chk && extra) chk.addEventListener('change', () => {
    extra.hidden = !chk.checked;
    extra.querySelectorAll('input,textarea').forEach(el => {
      if (chk.checked && ['shop_name','phone','district'].includes(el.name)) el.setAttribute('required','required');
      else el.removeAttribute('required');
    });
  });

  // Close
  overlay?.addEventListener('click', closeModal);
  closeBtn?.addEventListener('click', closeModal);

  // Nav triggers
  $$('[data-auth-open]').forEach(el=>{
    el.addEventListener('click', e=>{
      e.preventDefault();
      openModal(el.dataset.authOpen || 'login');
    });
  });

  // Flash toast (success/fail messages from controller)
  const flash = $("#auth-flash");
  if (flash) {
    const ok  = flash.dataset.ok === '1';
    const msg = flash.dataset.msg || '';
    const red = flash.dataset.redirect || '';
    if (msg) {
      const t = document.createElement('div');
      t.className = 'toast ' + (ok ? 'ok' : 'err');
      t.textContent = msg;
      document.body.appendChild(t);
      setTimeout(()=>t.remove(), 2500);
      if (ok && red) setTimeout(()=> window.location.href = red, 600);
    }
  }

  // Validation errors → open modal on the right tab and show a toast with errors
  const errs = $("#auth-errors");
  if (errs) {
    const which = errs.dataset.form || 'login';
    try {
      const list = JSON.parse(errs.dataset.errors || '[]');
      if (Array.isArray(list) && list.length) {
        openModal(which); // auto-open ONLY when errors exist
        const t = document.createElement('div');
        t.className = 'toast err';
        t.style.maxWidth = '420px';
        t.style.whiteSpace = 'pre-wrap';
        t.textContent = 'Please fix the following:\n• ' + list.join('\n• ');
        document.body.appendChild(t);
        setTimeout(()=>t.remove(), 4500);
      }
    } catch(e) {}
  }

  // helper for console testing
  window.__openAuth = openModal;
})();
</script>
<style>
  /* Emergency visibility override */
  #auth-overlay.is-open { display: block !important; }
  #auth-modal.is-open   { display: grid !important; z-index: 99999 !important; }
  #auth-modal.is-open .modal-card { display: block !important; }
</style>
<style>
  /* Put this AFTER your existing modal CSS (bottom of the partial or end of app.css) */
  #auth-modal.is-open   { opacity: 1 !important; }
  #auth-overlay.is-open { opacity: 1 !important; }

  /* (optional) if your base CSS sets overlay/modal opacity to 0 by default, keep it) */
  /* #auth-modal, #auth-overlay { opacity: 0; } */
</style>
<style>
  /* FORCE the open state to win over base app.css */
  #auth-modal.is-open {
    display: grid !important;
    opacity: 1 !important;
    visibility: visible !important;
    z-index: 2147483647 !important;  /* higher than overlay */
  }
  #auth-overlay.is-open {
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
    z-index: 2147483646 !important;  /* just below modal */
  }

  /* In case the card was animated hidden by theme CSS */
  #auth-modal.is-open .modal-card {
    opacity: 1 !important;
    transform: none !important;
  }
</style>
