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
}
