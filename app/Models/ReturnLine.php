<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnLine extends Model
{
    protected $fillable = ['sale_return_id', 'sale_line_id', 'product_id', 'quantity', 'type']; // type: restock, damage

    public function saleReturn()
    {
        return $this->belongsTo(SaleReturn::class);
    }

    public function saleLine()
    {
        return $this->belongsTo(SaleLine::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
