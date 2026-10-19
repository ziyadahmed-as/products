<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Rules\EthiopianPhone;
use App\Services\ActivityLogger;

class ProfileController extends Controller
{
    /**
     * Display the authenticated user's profile and security settings.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $user->load(['roles', 'branches']);
        $activeTab = $request->query('tab', session('active_tab', 'profile'));

        return view('profile.index', compact('user', 'activeTab'));
    }

    /**
     * Update the authenticated user's profile information.
     */
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($request->filled('phone')) {
            $request->merge(['phone' => str_replace(' ', '', $request->phone)]);
        }

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', new EthiopianPhone],
        ]);

        $oldAttributes = [
            'name'  => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
        ];

        $user->name  = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;
        $user->save();

        ActivityLogger::log(
            'Users',
            'update',
            "User {$user->name} updated their profile information",
            $user,
            $user,
            [
                'before' => $oldAttributes,
                'after'  => [
                    'name'  => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                ],
            ]
        );

        return redirect()
            ->route('profile.index', ['tab' => 'profile'])
            ->with('success', 'Profile information updated successfully.');
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ], [
            'password.different' => 'The new password must be different from your current password.',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()
                ->route('profile.index', ['tab' => 'password'])
                ->withErrors(['current_password' => 'The provided current password does not match your current password.'])
                ->withInput();
        }

        $user->password = Hash::make($request->password);
        $user->save();

        ActivityLogger::log(
            'Auth',
            'password_change',
            "User {$user->name} changed their account password",
            $user,
            $user
        );

        return redirect()
            ->route('profile.index', ['tab' => 'password'])
            ->with('success', 'Your password has been changed successfully.');
    }
}
