<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            function ($request, $next) {
                if (!auth()->user() || !auth()->user()->hasAnyRole(['Super Admin', 'Admin'])) {
                    abort(403, 'Access denied.');
                }
                return $next($request);
            },
        ];
    }

    public function index(Request $request)
    {
        $users = User::with(['roles', 'branches'])
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%"))
            ->when($request->role, fn($q) => $q->whereHas('roles', fn($r) => $r->where('name', $request->role)))
            ->when($request->filled('status'), fn($q) => $q->where('is_active', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $roles = Role::orderBy('name')->get();

        $summary = [
            'total'       => User::count(),
            'active'      => User::where('is_active', true)->count(),
            'inactive'    => User::where('is_active', false)->count(),
            'admins'      => User::whereHas('roles', fn($r) => $r->whereIn('name', ['Super Admin', 'Admin']))->count(),
        ];

        return view('users.index', compact('users', 'roles', 'summary'));
    }

    public function create()
    {
        $roles    = Role::orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        return view('users.create', compact('roles', 'branches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:120',
            'email'     => 'required|email|unique:users,email',
            'phone'     => 'nullable|string|max:30',
            'password'  => 'required|string|min:8|confirmed',
            'role'      => 'required|exists:roles,name',
            'branches'  => 'nullable|array',
            'branches.*'=> 'exists:branches,id',
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'] ?? null,
            'password'  => Hash::make($data['password']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $user->assignRole($data['role']);

        if (!empty($data['branches'])) {
            $user->branches()->sync($data['branches']);
        }

        return redirect()->route('users.index')->with('success', "User «{$user->name}» created successfully.");
    }

    public function show(User $user)
    {
        $user->load(['roles', 'branches']);
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles    = Role::orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $user->load(['roles', 'branches']);
        return view('users.edit', compact('user', 'roles', 'branches'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:120',
            'email'     => "required|email|unique:users,email,{$user->id}",
            'phone'     => 'nullable|string|max:30',
            'password'  => 'nullable|string|min:8|confirmed',
            'role'      => 'required|exists:roles,name',
            'branches'  => 'nullable|array',
            'branches.*'=> 'exists:branches,id',
            'is_active' => 'boolean',
        ]);

        $updateData = [
            'name'      => $data['name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);
        $user->syncRoles([$data['role']]);
        $user->branches()->sync($data['branches'] ?? []);

        return redirect()->route('users.index')->with('success', "User «{$user->name}» updated.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User removed.');
    }
}
