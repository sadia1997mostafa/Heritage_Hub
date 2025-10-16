<!doctype html>
<html>
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Mock Payment</title>
    <style>
      :root{
        --bg-1: linear-gradient(135deg,#fff9f5,#fbf4e8 40%, #fffaf6 70%);
        --muted:#6b6b6b;
        --shadow:0 18px 48px rgba(16,24,40,.12);
        --brand:#6B4E3D; /* brownish brand */
        --accent-bkash:#ff4d7e;
        --accent-nagad:#7b36ff;
        --accent-rocket:#ff9f43;
        --accent-card:#2d9cdb;
      }
      *{box-sizing:border-box}
      body{margin:0;font-family:Inter,system-ui,Arial,sans-serif;background:var(--bg-1);color:#222}
      .wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:40px}
      .card{width:min(820px,96vw);background:linear-gradient(180deg,#ffffffee,#ffffffcc);border-radius:16px;padding:26px;text-align:center;box-shadow:var(--shadow);border:1px solid rgba(0,0,0,0.03)}
      .muted{color:var(--muted)}
      .lead{font-size:18px;font-weight:700}
      .amount{font-size:20px;font-weight:800;color:#0b3b2e;margin-top:8px}
      .providers{display:flex;gap:12px;justify-content:center;margin-top:16px;flex-wrap:wrap}
  .provider{width:140px;height:70px;border-radius:12px;display:flex;align-items:center;justify-content:flex-start;gap:10px;padding:10px 12px;cursor:pointer;border:2px solid transparent;transition:all .18s ease;box-shadow:0 6px 18px rgba(16,24,40,.06);background:#fff}
  .provider .label{margin-left:6px;font-weight:700;color:var(--brand)}
  .provider.bkash{border-left:6px solid var(--accent-bkash)}
  .provider.nagad{border-left:6px solid var(--accent-nagad)}
  .provider.rocket{border-left:6px solid var(--accent-rocket)}
  .provider.card{border-left:6px solid var(--accent-card)}
      .provider.active{transform:translateY(-4px);border-color:rgba(0,0,0,0.06);box-shadow:0 14px 32px rgba(16,24,40,.12)}
      .actions{display:flex;gap:10px;justify-content:center;margin-top:18px}
      .btn{display:inline-block;padding:10px 16px;border-radius:10px;text-decoration:none;color:#fff;background:#0b6b5a;border:0;font-weight:700}
      .btn.ghost{background:transparent;color:#0b6b5a;border:1px solid rgba(11,107,90,.12)}
      .helper{margin-top:12px;color:var(--muted);font-size:13px}
      .toast{position:fixed;top:18px;right:18px;background:#0b6b5a;color:#fff;padding:10px 12px;border-radius:8px;box-shadow:var(--shadow);}
      .provider img{width:42px;height:42px;object-fit:contain}
    </style>
  </head>
  <body>
    <div class="wrap">
      <div class="card">
        <h2>Mock Payment Gateway</h2>
        <p class="muted">This is a local mock gateway for development. Intent: <strong>#{{ $intent->id }}</strong></p>
        <div style="margin-top:12px" class="lead">Payment</div>
        <div class="amount">{{ number_format($intent->amount/100,2) }} {{ $intent->currency }}</div>
        <div class="muted" style="margin-bottom:12px">Intent: <strong>#{{ $intent->id }}</strong> &nbsp; • &nbsp; Gateway: <strong>{{ $intent->gateway }}</strong></div>

        <div class="providers" role="list">
          <div class="provider bkash" data-gateway="bkash" role="listitem" title="bKash">
            <img src="{{ asset('images/bkash.png') }}" alt="bKash" />
            <div class="label">bKash</div>
          </div>

          <div class="provider nagad" data-gateway="nagad" role="listitem" title="Nagad">
            <img src="{{ asset('images/nagad.png') }}" alt="Nagad" />
            <div class="label">Nagad</div>
          </div>

          <div class="provider rocket" data-gateway="rocket" role="listitem" title="Rocket">
            <img src="{{ asset('images/rocket.png') }}" alt="Rocket" />
            <div class="label">Rocket</div>
          </div>

          <div class="provider card" data-gateway="card" role="listitem" title="Card (SSLCommerz)">
            <img src="{{ asset('images/card.png') }}" alt="Card" />
            <div class="label">Card</div>
          </div>
        </div>

        <div class="actions">
          <form id="mock-success-form" method="GET" action="{{ route('payment.mock.return', ['id'=>$intent->id, 'action'=>'success']) }}" style="display:inline">
            <input type="hidden" name="gateway" id="success-gateway" value="mock" />
            <input type="hidden" id="mock-gateway" value="mock" />
            <button class="btn" id="mock-success">Complete payment</button>
          </form>

          <form id="mock-cancel-form" method="GET" action="{{ route('payment.mock.return', ['id'=>$intent->id, 'action'=>'cancel']) }}" style="display:inline">
            <input type="hidden" name="gateway" id="cancel-gateway" value="mock" />
            <button class="btn ghost" id="mock-cancel">Cancel</button>
          </form>

          <button class="btn ghost" id="mock-autosubmit" title="Auto-submit after 2s">Auto-submit</button>
        </div>

        <div id="provider-ui" style="margin-top:18px"></div>
        <div class="helper">Click a provider to select — then complete payment or use the simulated webhook.</div>
        <div style="margin-top:14px;text-align:center">
          <button class="btn" id="send-webhook">Simulate provider webhook</button>
          <div class="muted" style="margin-top:8px;font-size:13px">(sends a fake POST to /payments/webhook to help test webhooks)</div>
        </div>
      </div>
    </div>

    <div id="mock-toast" aria-hidden="true"></div>

    <script>
      function showToast(msg,timeout=1800){
        const c = document.getElementById('mock-toast');
        const el = document.createElement('div'); el.className='toast'; el.textContent=msg; c.appendChild(el);
        setTimeout(()=>el.remove(), timeout);
      }

      document.getElementById('mock-success').addEventListener('click', function(e){
        e.preventDefault(); showToast('Processing payment…'); setTimeout(()=> { e.target.closest('form').submit(); }, 700);
      });
      document.getElementById('mock-cancel').addEventListener('click', function(e){
        e.preventDefault(); showToast('Cancelling…'); setTimeout(()=> { e.target.closest('form').submit(); }, 700);
      });
      document.getElementById('mock-autosubmit').addEventListener('click', function(e){
        showToast('Auto-submit in 2s…'); setTimeout(()=> { document.querySelector('form[action*="success"]').submit(); }, 2000);
      });

      // wire provider tiles
      // store current intent id for ajax calls
      var HH_INTENT_ID = '{{ $intent->id }}';

      document.querySelectorAll('.provider').forEach(function(node){
        node.addEventListener('click', function(){
          var g = node.getAttribute('data-gateway');
          // update hidden fields
          document.getElementById('success-gateway').value = g;
          document.getElementById('cancel-gateway').value = g;
          // set mock-gateway input value too
          var mg = document.getElementById('mock-gateway'); if (mg) mg.value = g;
          // visual active state
          document.querySelectorAll('.provider').forEach(n=>n.classList.remove('active'));
          node.classList.add('active');
          showToast('Selected: '+g, 700);
          renderProviderUI(g);

          // If bkash selected, start the bkash create/approve flow: POST to bkash.create and redirect to approve URL
          if (g === 'bkash') {
            showToast('Opening bKash checkout…', 900);
            fetch('{{ route('bkash.create') }}', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
              },
              body: JSON.stringify({ intent: HH_INTENT_ID })
            }).then(function(res){
              return res.json();
            }).then(function(data){
              if (data && data.approveUrl) {
                // redirect to approval URL (mock approve page or provider URL)
                window.location.href = data.approveUrl;
              } else {
                showToast('Could not open bKash checkout', 1600);
              }
            }).catch(function(err){
              console.error(err); showToast('bKash checkout failed', 1600);
            });
          }
        });
      });

      // Provider-specific UI renderer
      function renderProviderUI(gateway) {
        var container = document.getElementById('provider-ui');
        container.innerHTML = '';
        if (gateway === 'bkash' || gateway === 'nagad' || gateway === 'rocket') {
          var html = `
            <div style="display:flex;gap:8px;justify-content:center;align-items:center">
              <input id="provider-phone" placeholder="Enter phone (e.g. +8801...)" style="padding:8px;border-radius:8px;border:1px solid rgba(0,0,0,.06)" />
              <button class="btn ghost" id="send-otp">Request OTP</button>
            </div>
            <div style="margin-top:8px;display:flex;gap:8px;justify-content:center;align-items:center">
              <input id="provider-otp" placeholder="Enter OTP" style="padding:8px;border-radius:8px;border:1px solid rgba(0,0,0,.06)" />
              <button class="btn" id="verify-otp">Verify OTP</button>
            </div>
            <div id="provider-status" style="margin-top:8px;text-align:center" class="muted"></div>
          `;
          container.innerHTML = html;

          document.getElementById('send-otp').addEventListener('click', function(e){
            var phone = document.getElementById('provider-phone').value || '';
            if (!phone) { showToast('Enter a phone number', 900); return; }
            showToast('OTP sent to '+phone, 1200);
            document.getElementById('provider-status').textContent = 'OTP sent — use code 1234 to simulate';
          });

          document.getElementById('verify-otp').addEventListener('click', function(e){
            var otp = document.getElementById('provider-otp').value || '';
            if (otp === '1234') {
              showToast('OTP verified', 800);
              document.getElementById('provider-status').textContent = 'Verified — you may now complete payment or send webhook.';
            } else {
              showToast('Invalid OTP (use 1234)', 1200);
            }
          });
        } else if (gateway === 'card') {
          container.innerHTML = '<div class="muted">Card flow (SSLCommerz) is simulated by redirecting to the provider path; use real sandbox keys to test.</div>';
        } else {
          container.innerHTML = '<div class="muted">Using fallback mock flow — select a gateway to see provider-specific simulation.</div>';
        }
      }

      // initial render based on default value
      renderProviderUI(document.getElementById('mock-gateway').value);

      // Simulate provider webhook: POST to our webhook endpoint
      document.getElementById('send-webhook').addEventListener('click', function(e){
        var gateway = document.getElementById('mock-gateway').value;
        var external = '{{ $intent->external_id }}';
        var eventId = 'evt-' + Math.random().toString(36).substring(2,9);
        var payload = { external_id: external, event: 'payment.succeeded', event_id: eventId, gateway: gateway };
        showToast('Sending simulated webhook…');
        fetch('{{ route('payment.webhook') }}', { method:'POST', headers: { 'Content-Type': 'application/json','X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify(payload) })
          .then(r=>r.text().then(t=>{ showToast('Webhook response: '+r.status+' '+t, 2200); }))
          .catch(err=>{ showToast('Failed to send webhook', 1800); console.error(err); });
      });
    </script>
  </body>
</html>
