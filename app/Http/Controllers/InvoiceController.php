<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function show(Sale $sale)
    {
        // Ensure relations are loaded
        $sale->load(['lines.product', 'user', 'branch', 'storageLocation', 'payments']);
        
        return view('invoice', compact('sale'));
    }
}
