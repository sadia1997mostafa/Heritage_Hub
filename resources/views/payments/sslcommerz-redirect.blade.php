<!doctype html>
<html>
  <head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Redirecting to SSLCommerz</title></head>
  <body>
    <p>Redirecting to payment gateway…</p>
    <form id="sslform" method="POST" action="{{ $endpoint }}">
      @foreach($payload as $k=>$v)
        <input type="hidden" name="{{ $k }}" value="{{ $v }}" />
      @endforeach
      <noscript>
        <button type="submit">Continue to payment</button>
      </noscript>
    </form>
    <script>document.getElementById('sslform').submit();</script>
  </body>
</html>
