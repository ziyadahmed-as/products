<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeMaterial extends Model
{
    protected $fillable = ['recipe_id', 'raw_material_id', 'quantity'];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    public function rawMaterial()
    {
        return $this->belongsTo(Product::class, 'raw_material_id');
    }
}
