<!doctype html>
<html>
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Redirecting to bKash Sandbox</title>
    <style>body{font-family:Inter,system-ui,Arial,sans-serif;display:grid;place-items:center;height:100vh;background:linear-gradient(180deg,#fff,#fbf4e8)}.card{padding:20px;border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,.06);background:#fff;max-width:640px;text-align:center}</style>
  </head>
  <body>
    <div class="card">
      <h3>Redirecting to bKash sandbox…</h3>
      <p class="muted">You will be forwarded to the bKash sandbox to complete payment. This is a simulated flow for development.</p>
      <form id="bkash-form" method="POST" action="{{ $endpoint }}">
        @foreach($payload as $k=>$v)
          <input type="hidden" name="{{ $k }}" value="{{ $v }}" />
        @endforeach
        <button type="submit" class="btn">Continue to bKash</button>
      </form>
    </div>
    <script>setTimeout(()=>document.getElementById('bkash-form').submit(),900)</script>
  </body>
</html>
