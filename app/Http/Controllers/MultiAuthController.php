<?php
// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Admin;
use App\Models\VendorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;

class MultiAuthController extends Controller
{
    /**
     * Register a new User or Vendor
     */
    public function register(Request $req)
    {
        Log::info('AUTH register hit', [
            'email'     => $req->input('email'),
            'as_vendor' => $req->boolean('register_as_vendor'),
        ]);

        $isVendor = $req->boolean('register_as_vendor');

        // ---------- Validation ----------
        $rules = [
            'name'                  => ['required','string','max:255'],
            'email'                 => ['required','email','max:255','unique:users,email'],
            'password'              => ['required', Password::min(8)->letters()->numbers(), 'confirmed'],
            'avatar'                => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ];

        if ($isVendor) {
            $rules += [
                'shop_name'       => ['required','string','max:255'],
                'description'     => ['nullable','string'],
                'heritage_story'  => ['nullable','string'],
                'address'         => ['nullable','string','max:255'],
                'phone'           => ['required','string','max:30'],
                'district_id'     => ['required','integer','exists:districts,id'],
                'shop_logo'       => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
                'vendor_category' => ['required_if:register_as_vendor,1','string','max:120'],
            ];
        }

        $data = $req->validate($rules);

        // ---------- Avatar upload (optional) ----------
        $avatarPath = null;
        if ($req->hasFile('avatar')) {
            $avatarPath = $req->file('avatar')->store('avatars', 'public');
        }

        // ---------- Create User ----------
        $insert = [
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ];

        if (Schema::hasColumn('users','profile_photo_path')) {
            $insert['profile_photo_path'] = $avatarPath;
        }

        $user = User::create($insert);
        Log::info('AUTH register success', ['id' => $user->id]);

        // ---------- If vendor: create vendor profile ----------
        if ($isVendor) {
            $logoPath = null;
            if ($req->hasFile('shop_logo')) {
                $logoPath = $req->file('shop_logo')->store('vendors/logos', 'public');
            }

            VendorProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'shop_name'       => $data['shop_name'],
                    'slug'            => Str::slug($data['shop_name']).'-'.Str::random(4),
                    'status'          => 'pending',
                    'approved_at'     => null,
                    'rejected_at'     => null,
                    'rejection_reason'=> null,
                    'description'     => $data['description']    ?? null,
                    'heritage_story'  => $data['heritage_story'] ?? null,
                    'address'         => $data['address']        ?? null,
                    'phone'           => $data['phone'],
                    'district_id'     => (int)$data['district_id'],
                    'vendor_category' => $data['vendor_category'], // ✅ hardcoded category saved here
                    'shop_logo_path'  => $logoPath,
                ]
            );

            Auth::guard('vendor')->login($user);
            $req->session()->regenerate();

            return redirect()
                ->intended(route('vendor.dashboard'))
                ->with([
                    'auth_ok'   => true,
                    'auth_role' => 'vendor',
                    'auth_msg'  => 'Registered as Vendor. Your store is pending admin approval.',
                ]);
        }

        // ---------- Default: normal user ----------
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

    /**
     * Login (User, Vendor, Admin)
     */
    public function login(Request $req)
    {
        Log::info('AUTH login hit', [
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
                return back()->withErrors(['login' => 'Account not registered as Admin.']);
            }

            if (Auth::guard('admin')->attempt($cred, $remember)) {
                $req->session()->regenerate();
                return redirect()->intended(route('admin.dashboard'))
                    ->with(['auth_ok'=>true,'auth_role'=>'admin','auth_msg'=>'Successfully logged in as Admin.']);
            }

            return back()->withErrors(['login' => 'Invalid credentials.']);
        }

        // VENDOR
        if ($data['login_as'] === 'vendor') {
            $user = User::where('email', $email)->first();

            if (!$user) {
                return back()->withErrors(['login' => 'Account not registered.']);
            }

            if (!$user->vendorProfile) {
                return back()->withErrors(['login' => 'You are not registered as Vendor.']);
            }

            if (Auth::guard('vendor')->attempt($cred, $remember)) {
                $req->session()->regenerate();
                return redirect()->intended(route('vendor.dashboard'))
                    ->with(['auth_ok'=>true,'auth_role'=>'vendor','auth_msg'=>'Successfully logged in as Vendor.']);
            }

            return back()->withErrors(['login' => 'Invalid credentials.']);
        }

        // USER (default)
        if (!User::where('email', $email)->exists()) {
            return back()->withErrors(['login' => 'Account not registered.']);
        }

        if (Auth::guard('web')->attempt($cred, $remember)) {
            $req->session()->regenerate();
            return redirect()->intended(route('home'))
                ->with(['auth_ok'=>true,'auth_role'=>'user','auth_msg'=>'Successfully logged in as User.']);
        }

        return back()->withErrors(['login' => 'Invalid credentials.']);
    }

    /**
     * Logout for any role
     */
    public function logout(Request $req)
    {
        if (Auth::guard('admin')->check())  { Auth::guard('admin')->logout(); }
        if (Auth::guard('vendor')->check()) { Auth::guard('vendor')->logout(); }
        if (Auth::guard('web')->check())    { Auth::guard('web')->logout(); }

        $req->session()->invalidate();
        $req->session()->regenerateToken();

        return redirect()->route('home')
            ->with(['auth_ok' => true, 'auth_msg' => 'Logged out.']);
    }
}
