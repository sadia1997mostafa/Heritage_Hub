<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('title', config('app.name'))</title>
  @stack('styles')
  <style>
    .container{max-width:1100px;margin:0 auto;padding:0 16px}
    @media (max-width:900px){
      main{padding:12px}
    }
    img,video{max-width:100%;height:auto}
  </style>
</head>
<body style="margin:0;padding:0;background:#fff;color:#000;font-family:Arial,Helvetica,sans-serif;">
  <main>
    @yield('content')
  </main>
  @stack('scripts')
</body>
</html>
