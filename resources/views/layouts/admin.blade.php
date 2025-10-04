<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Panel — @yield('title','Dashboard')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @vite(['resources/css/admin.css'])
</head>
<body>
  <div class="admin-shell">
    <aside class="admin-aside">
      <div class="admin-brand">ADMIN PANEL</div>
      <nav class="admin-nav">
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard')?'active':'' }}">Dashboard</a>
        <a href="{{ route('admin.vendors.index') }}" class="{{ request()->routeIs('admin.vendors.*')?'active':'' }}">Vendors</a>
        <a href="{{ route('admin.payouts.index') }}" class="{{ request()->routeIs('admin.payouts.*')?'active':'' }}">Payouts</a>
        <a href="{{ route('home') }}">← Back to Site</a>
      </nav>
    </aside>

    <main class="admin-main">
      <div class="admin-topcard">
        <div>Welcome, <strong>{{ auth('admin')->user()->name ?? 'Admin' }}</strong></div>
        <form method="POST" action="{{ route('auth.logout') }}">@csrf
          <button class="admin-logout">Logout</button>
        </form>
      </div>

      <h1 class="page-title">@yield('title','Dashboard')</h1>

      {{-- page body --}}
      @yield('content')
    </main>
  </div>
</body>
</html>
