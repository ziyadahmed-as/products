<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class);
    }

    /**
     * Return the branch IDs this user is authorized to sell from.
     * - Super Admin: all active branches
     * - Manager: branches explicitly assigned via branch_user pivot
     * - Seller: branches explicitly assigned via branch_user pivot
     */
    public function authorizedBranchIds(): \Illuminate\Support\Collection
    {
        if ($this->hasRole('Super Admin')) {
            return Branch::where('is_active', true)->pluck('id');
        }

        return $this->branches()->pluck('branches.id');
    }

    /**
     * Check whether this user is allowed to operate in the given branch.
     */
    public function canAccessBranch(int $branchId): bool
    {
        return $this->authorizedBranchIds()->contains($branchId);
    }

    /**
     * Get the default landing dashboard URL based on the user's role.
     * - Super Admin -> /admin (Filament Admin Panel Dashboard)
     * - Manager -> /admin (Filament Manager Workspace) or /dashboard
     * - Seller -> /admin/quick-sells (Filament Quick Sell / POS)
     * - Production Officer -> /admin/manufacturing-orders
     * - Client / Buyer / Customer -> /client/dashboard
     */
    public function getDashboardUrl(): string
    {
        if ($this->hasRole('Super Admin')) {
            return '/admin';
        }

        if ($this->hasRole('Manager')) {
            return '/admin';
        }

        if ($this->hasRole('Seller')) {
            return '/admin/quick-sells';
        }

        if ($this->hasRole('Production Officer')) {
            return '/admin/manufacturing-orders';
        }

        if ($this->hasRole('Auditor')) {
            return '/admin/activities';
        }

        if ($this->hasRole('Client')) {
            return route('client.dashboard');
        }

        // Default internal users
        if ($this->roles()->count() > 0) {
            return '/admin';
        }

        return route('client.dashboard');
    }
}
