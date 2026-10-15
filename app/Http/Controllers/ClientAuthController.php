<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use App\Models\User;
use App\Models\Sale;

class ClientAuthController extends Controller
{
    /* ─────────────────────────── LOGIN ─────────────────────────── */

    public function showLogin()
    {
        return view('public.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Claim any guest orders that match this email
            $this->claimOrders($user);

            // Direct staff roles (Super Admin, Manager, Seller, Production Officer, Auditor) directly to their dashboard
            if ($user->hasAnyRole(['Super Admin', 'Manager', 'Seller', 'Production Officer', 'Product Manufacturer', 'Production', 'Auditor'])) {
                $request->session()->forget('url.intended');
                return redirect()->to($user->getDashboardUrl());
            }

            return redirect()->intended($user->getDashboardUrl());
        }

        return back()->withErrors(['email' => 'These credentials do not match our records.'])->onlyInput('email');
    }

    /* ─────────────────────────── REGISTER ─────────────────────────── */

    public function showRegister()
    {
        return view('public.auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'phone'                 => 'nullable|string|max:30',
            'password'              => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'password'  => Hash::make($request->password),
            'is_active' => true,
        ]);

        // Assign 'Client' role if it exists
        if (\Spatie\Permission\Models\Role::where('name', 'Client')->exists()) {
            $user->assignRole('Client');
        }

        Auth::login($user);

        // Claim any past orders placed with this email (guest checkout)
        $this->claimOrders($user);

        // Clear the last_order session so the prompt doesn't keep showing
        session()->forget(['last_order_reference', 'last_order_email', 'last_order_name']);

        return redirect()->route('client.dashboard')->with('success', 'Welcome! Your account has been created and your orders have been linked.');
    }

    /* ─────────────────────────── LOGOUT ─────────────────────────── */

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('public.home');
    }

    /* ─────────────────────────── FORGOT PASSWORD ─────────────────────────── */

    public function showForgotPassword()
    {
        return view('public.auth.forgot_password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(string $token)
    {
        return view('public.auth.reset_password', ['token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'                 => 'required',
            'email'                 => 'required|email',
            'password'              => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('client.login')->with('status', 'Your password has been reset! Please log in.')
            : back()->withErrors(['email' => __($status)]);
    }

    /* ─────────────────────────── HELPERS ─────────────────────────── */

    /**
     * Link all unowned orders with this user's email to their account.
     */
    private function claimOrders(User $user): void
    {
        Sale::where('customer_email', strtolower(trim($user->email)))
            ->whereNull('client_id')
            ->where('type', 'customer_order')
            ->update(['client_id' => $user->id]);
    }
}
