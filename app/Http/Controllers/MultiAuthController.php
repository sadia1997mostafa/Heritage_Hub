<?php
// app/Http/Controllers/MultiAuthController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Admin;
use App\Models\VendorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class MultiAuthController extends Controller
{
   
   public function register(Request $req)
{
    \Log::info('AUTH register hit', [
        'email'    => $req->input('email'),
        'as_vendor'=> $req->boolean('register_as_vendor'),
    ]);

    $isVendor = $req->boolean('register_as_vendor');

    // base rules
    $rules = [
        'name'     => ['required','string','max:255'],
        'email'    => ['required','email','max:255','unique:users,email'],
        'password' => ['required', \Illuminate\Validation\Rules\Password::min(8)->letters()->numbers(), 'confirmed'],
        'avatar'   => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
    ];

    // vendor extras
    if ($isVendor) {
        $rules += [
            'shop_name'      => ['required','string','max:255'],
            'description'    => ['nullable','string'],
            'heritage_story' => ['nullable','string'],
            'address'        => ['nullable','string','max:255'],
            'phone'          => ['required','string','max:30'],
            'district'       => ['required','string','max:100'],
            'shop_logo'      => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ];
    }

  try {
    $data = $req->validate($rules);
    \Log::info('AUTH validation passed', $data);
} catch (\Illuminate\Validation\ValidationException $e) {
    \Log::error('AUTH validation failed', [
        'errors' => $e->errors()
    ]);
    throw $e; // rethrow so Laravel still redirects with errors
}

    // upload avatar
    $avatarPath = null;
    if ($req->hasFile('avatar')) {
        $avatarPath = $req->file('avatar')->store('avatars', 'public');
    }

    try {
        // create user safely
        $insert = [
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ];
        if (\Schema::hasColumn('users','profile_photo_path')) {
            $insert['profile_photo_path'] = $avatarPath;
        }

        $user = User::create($insert);

        \Log::info('AUTH register success', ['id' => $user->id]);

    } catch (\Throwable $e) {
        \Log::error('AUTH register failed', [
            'email' => $req->input('email'),
            'error' => $e->getMessage(),
        ]);

        return back()
            ->withInput($req->except(['password','password_confirmation']))
            ->withErrors(['register' => 'Registration failed: '.$e->getMessage()]);
    }

    // if vendor, create vendor profile
    if ($isVendor) {
        $logoPath = null;
        if ($req->hasFile('shop_logo')) {
            $logoPath = $req->file('shop_logo')->store('shop-logos', 'public');
        }

        VendorProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'shop_name'      => $data['shop_name'],
                'description'    => $data['description'] ?? null,
                'heritage_story' => $data['heritage_story'] ?? null,
                'address'        => $data['address'] ?? null,
                'phone'          => $data['phone'],
                'district'       => $data['district'],
                'shop_logo_path' => $logoPath,
            ]
        );

        Auth::guard('vendor')->login($user);
        $req->session()->regenerate();

        return redirect()
            ->intended(route('vendor.dashboard'))
            ->with([
                'auth_ok'   => true,
                'auth_role' => 'vendor',
                'auth_msg'  => 'Successfully registered as Vendor.',
            ]);
    }

    // default: normal user → go home
    Auth::guard('web')->login($user);
    $req->session()->regenerate();

    return redirect()
        ->intended(route('home'))
        ->with([
            'auth_ok'   => true,
            'auth_role' => 'user',
            'auth_msg'  => 'Successfully registered as User.',
        ]);
}

   public function login(Request $req)
{
    \Log::info('AUTH login hit', [
        'email'    => $req->input('email'),
        'login_as' => $req->input('login_as'),
    ]);

    $data = $req->validate([
        'email'    => ['required','email'],
        'password' => ['required'],
        'login_as' => ['required','in:user,vendor,admin'],
        'remember' => ['nullable','boolean'],
    ]);

    $email    = $data['email'];
    $cred     = ['email' => $email, 'password' => $data['password']];
    $remember = (bool) ($data['remember'] ?? false);

    // ADMIN
    if ($data['login_as'] === 'admin') {
        if (!Admin::where('email', $email)->exists()) {
            return back()
                ->withErrors(['login' => 'Account not registered as Admin.'])
                ->withInput(['email' => $email, 'login_as' => 'admin']);
        }

        if (Auth::guard('admin')->attempt($cred, $remember)) {
            $req->session()->regenerate();

            return redirect()
                ->intended(route('admin.dashboard'))
                ->with([
                    'auth_ok'   => true,
                    'auth_role' => 'admin',
                    'auth_msg'  => 'Successfully logged in as Admin.',
                ]);
        }

        return back()
            ->withErrors(['login' => 'Invalid credentials.'])
            ->withInput(['email' => $email, 'login_as' => 'admin']);
    }

    // VENDOR
    if ($data['login_as'] === 'vendor') {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()
                ->withErrors(['login' => 'Account not registered.'])
                ->withInput(['email' => $email, 'login_as' => 'vendor']);
        }

        if (!$user->vendorProfile) {
            return back()
                ->withErrors(['login' => 'You are not registered as Vendor.'])
                ->withInput(['email' => $email, 'login_as' => 'vendor']);
        }

        if (Auth::guard('vendor')->attempt($cred, $remember)) {
            $req->session()->regenerate();

            return redirect()
                ->intended(route('vendor.dashboard'))
                ->with([
                    'auth_ok'   => true,
                    'auth_role' => 'vendor',
                    'auth_msg'  => 'Successfully logged in as Vendor.',
                ]);
        }

        return back()
            ->withErrors(['login' => 'Invalid credentials.'])
            ->withInput(['email' => $email, 'login_as' => 'vendor']);
    }

    // USER (default)
    if (!User::where('email', $email)->exists()) {
        return back()
            ->withErrors(['login' => 'Account not registered.'])
            ->withInput(['email' => $email, 'login_as' => 'user']);
    }

    if (Auth::guard('web')->attempt($cred, $remember)) {
        $req->session()->regenerate();

        return redirect()
            ->intended(route('home')) // users go to the Home page
            ->with([
                'auth_ok'   => true,
                'auth_role' => 'user',
                'auth_msg'  => 'Successfully logged in as User.',
            ]);
    }

    return back()
        ->withErrors(['login' => 'Invalid credentials.'])
        ->withInput(['email' => $email, 'login_as' => 'user']);
}

    public function logout(Request $req)
    {
        // log out whichever guard is active
        if (Auth::guard('admin')->check())  { Auth::guard('admin')->logout(); }
        if (Auth::guard('vendor')->check()) { Auth::guard('vendor')->logout(); }
        if (Auth::guard('web')->check())    { Auth::guard('web')->logout(); }

        $req->session()->invalidate();
        $req->session()->regenerateToken();

        // after logout everyone returns to Home
        return redirect()
            ->route('home')
            ->with(['auth_ok' => true, 'auth_msg' => 'Logged out.']);
    }
}
