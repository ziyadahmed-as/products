<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['name', 'code', 'type', 'parent_branch_id', 'is_active'];

    public function parent()
    {
        return $this->belongsTo(Branch::class, 'parent_branch_id');
    }

    public function children()
    {
        return $this->hasMany(Branch::class, 'parent_branch_id');
    }

    public function storageLocations()
    {
        return $this->hasMany(StorageLocation::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
