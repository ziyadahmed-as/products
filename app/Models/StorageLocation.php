<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StorageLocation extends Model
{
    protected $fillable = ['name', 'code', 'branch_id', 'is_active'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
