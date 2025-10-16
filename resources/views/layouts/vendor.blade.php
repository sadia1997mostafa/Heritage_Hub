<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Vendor Panel — @yield('title','Dashboard')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  {{-- HeritageHub vendor theme --}}
  @vite('resources/css/vendor.css')

</head>
<body>

  <div class="vendor-layout">

    {{-- Sidebar --}}
    <aside class="vendor-sidebar">
      <div class="vendor-sidebar__title">Vendor Panel</div>
      <nav>
        <a href="{{ route('vendor.dashboard') }}"
           class="nav-link {{ request()->routeIs('vendor.dashboard') ? 'active' : '' }}">
          Dashboard
        </a>
        <a href="{{ route('vendor.store.setup') }}"
           class="nav-link {{ request()->routeIs('vendor.store.*') ? 'active' : '' }}">
          Store Profile
        </a>
        <a href="{{ route('vendor.orders.index') }}"
           class="nav-link {{ request()->routeIs('vendor.orders.*') ? 'active' : '' }}">
          Orders
        </a>
        <a href="{{ route('vendor.returns.index') }}"
           class="nav-link {{ request()->routeIs('vendor.returns.*') ? 'active' : '' }}">
          Return Requests
        </a>
        <a href="{{ route('vendor.products.index') }}"
   class="{{ request()->routeIs('vendor.products.*') ? 'active' : '' }}">
  Products
</a>

        <a href="{{ route('vendor.payout.form') }}"
           class="nav-link {{ request()->routeIs('vendor.payout.*') ? 'active' : '' }}">
          Payout
        </a>
        <a href="{{ route('home') }}" class="nav-link">← Back to Site</a>
      </nav>
    </aside>

    {{-- Main area --}}
    <main class="vendor-content">

      {{-- Header bar --}}
      <div class="vendor-header">
        @php $vendorUser = request()->user('vendor') ?? auth()->user(); @endphp
        <div>Welcome, <strong>{{ $vendorUser->name ?? 'Vendor' }}</strong></div>
        <form method="POST" action="{{ route('auth.logout') }}">
          @csrf
          <button class="btn btn-ghost" type="submit">Logout</button>
        </form>
      </div>

      {{-- Page content --}}
      <div class="container">
        @yield('content')
      </div>

    </main>
  </div>

  {{-- Optional toast slot --}}
  @if(session('status'))
    <div class="toast">{{ session('status') }}</div>
  @endif

</body>
</html>
