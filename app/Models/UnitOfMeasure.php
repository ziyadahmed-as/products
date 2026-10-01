<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitOfMeasure extends Model
{
    protected $fillable = ['name', 'code'];

    public function products()
    {
        return $this->hasMany(Product::class, 'unit_of_measure_id');
    }
}
