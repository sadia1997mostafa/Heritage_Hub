<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    @php
        // Who's logged in? (check all guards)
        $currentUser = Auth::user() ?? Auth::guard('vendor')->user() ?? Auth::guard('admin')->user();

        // Which guard is active? (for route choices / badges)
        $activeGuard = Auth::guard('admin')->check()
            ? 'admin'
            : (Auth::guard('vendor')->check() ? 'vendor' : (Auth::check() ? 'web' : null));

        // Dashboard route based on guard (fallback to user dashboard if unknown)
        $dashboardRoute = match ($activeGuard) {
            'admin'  => (Route::has('admin.dashboard')  ? route('admin.dashboard')  : (Route::has('dashboard') ? route('dashboard') : '#')),
            'vendor' => (Route::has('vendor.dashboard') ? route('vendor.dashboard') : (Route::has('dashboard') ? route('dashboard') : '#')),
            'web'    => (Route::has('user.dashboard')   ? route('user.dashboard')   : (Route::has('dashboard') ? route('dashboard') : '#')),
            default  => (Route::has('dashboard') ? route('dashboard') : '#'),
        };

        // Nice label to show beside the name
        $roleLabel = match ($activeGuard) {
            'admin'  => 'Admin',
            'vendor' => 'Vendor',
            'web'    => 'User',
            default  => null,
        };
    @endphp

    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ $dashboardRoute }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="$dashboardRoute" :active="request()->url() === $dashboardRoute">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings / Auth Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @if($currentUser)
                    <x-dropdown align="right" width="56">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-2 px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-600 bg-white hover:text-gray-800 focus:outline-none transition ease-in-out duration-150">
                                {{-- Avatar (optional) --}}
                                @php
                                    $avatarUrl = method_exists($currentUser, 'getAvatarUrlAttribute')
                                        ? $currentUser->avatar_url
                                        : (property_exists($currentUser, 'profile_photo_path') && $currentUser->profile_photo_path
                                            ? asset('storage/'.$currentUser->profile_photo_path)
                                            : null);
                                @endphp
                                @if($avatarUrl)
                                    <img src="{{ $avatarUrl }}" alt="avatar" class="h-6 w-6 rounded-full object-cover">
                                @endif

                                <div class="flex items-center gap-2">
                                    <span>{{ $currentUser->name }}</span>
                                    @if($roleLabel)
                                        <span class="text-[11px] px-2 py-0.5 rounded-full bg-gray-100 border border-gray-200 text-gray-600">{{ $roleLabel }}</span>
                                    @endif
                                </div>

                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            @if(Route::has('profile.edit'))
                                <x-dropdown-link :href="route('profile.edit')">
                                    {{ __('Profile') }}
                                </x-dropdown-link>
                            @endif

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('auth.logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('auth.logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <!-- Not logged in: open your popup modal -->
                    <a href="#" data-auth-open="login" class="px-3 py-2 text-sm text-gray-600 hover:text-gray-800">Login</a>
                    <a href="#" data-auth-open="register" class="px-3 py-2 text-sm text-white bg-gray-800 rounded-md hover:bg-black">Sign Up</a>
                @endif
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-600 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="$dashboardRoute" :active="request()->url() === $dashboardRoute">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                @if($currentUser)
                    <div class="font-medium text-base text-gray-800">{{ $currentUser->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ $currentUser->email }}</div>
                @endif
            </div>

            <div class="mt-3 space-y-1">
                @if(Route::has('profile.edit'))
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>
                @endif

                @if($currentUser)
                    <!-- Authenticated: logout -->
                    <form method="POST" action="{{ route('auth.logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('auth.logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                @else
                    <!-- Guest: open modal -->
                    <a href="#" data-auth-open="login" class="block px-4 py-2 text-sm text-gray-700">Login</a>
                    <a href="#" data-auth-open="register" class="block px-4 py-2 text-sm text-gray-700">Sign Up</a>
                @endif
            </div>
        </div>
    </div>
</nav>
