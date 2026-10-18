<?php

namespace App\Http\Controllers;

use App\Models\SaleReturn;
use App\Models\Sale;
use Illuminate\Http\Request;

class SaleReturnController extends Controller
{
    public function index(Request $request)
    {
        $returns = SaleReturn::with(['sale.lines.product', 'user', 'lines.product'])
            ->when($request->search, fn($q) => $q->whereHas('sale', fn($s) => $s->where('id', 'like', "%{$request->search}%"))
                ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$request->search}%")))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'total'      => SaleReturn::count(),
            'pending'    => SaleReturn::where('status', 'pending')->count(),
            'approved'   => SaleReturn::where('status', 'approved')->count(),
            'refunded'   => SaleReturn::where('is_refund_processed', true)->count(),
            'total_amount' => SaleReturn::where('status', 'approved')->sum('refund_amount'),
        ];

        return view('sale_returns.index', compact('returns', 'summary'));
    }

    public function create()
    {
        $sales = Sale::with('lines.product')->latest()->limit(100)->get();
        return view('sale_returns.create', compact('sales'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sale_id'       => 'required|exists:sales,id',
            'reason'        => 'required|string|max:500',
            'refund_amount' => 'required|numeric|min:0',
        ]);

        $return = SaleReturn::create([
            'sale_id'       => $data['sale_id'],
            'user_id'       => auth()->id(),
            'reason'        => $data['reason'],
            'status'        => 'pending',
            'refund_amount' => $data['refund_amount'],
            'is_refund_processed' => false,
        ]);

        return redirect()->route('sale-returns.show', $return)->with('success', 'Return request created. Awaiting approval.');
    }

    public function show(SaleReturn $saleReturn)
    {
        $saleReturn->load(['sale.lines.product', 'user', 'lines.product']);
        return view('sale_returns.show', compact('saleReturn'));
    }

    public function edit(SaleReturn $saleReturn)
    {
        if ($saleReturn->status !== 'pending') {
            return back()->with('error', 'Only pending returns can be edited.');
        }
        $sales = Sale::with('lines.product')->latest()->limit(100)->get();
        return view('sale_returns.edit', compact('saleReturn', 'sales'));
    }

    public function update(Request $request, SaleReturn $saleReturn)
    {
        $data = $request->validate([
            'reason'               => 'required|string|max:500',
            'refund_amount'        => 'required|numeric|min:0',
            'status'               => 'required|in:pending,approved,rejected',
            'is_refund_processed'  => 'boolean',
        ]);

        $saleReturn->update([
            ...$data,
            'is_refund_processed' => $request->boolean('is_refund_processed'),
        ]);

        return redirect()->route('sale-returns.index')->with('success', 'Return updated.');
    }

    public function destroy(SaleReturn $saleReturn)
    {
        if ($saleReturn->status === 'approved' && $saleReturn->is_refund_processed) {
            return back()->with('error', 'Cannot delete a processed return.');
        }

        $saleReturn->delete();
        return redirect()->route('sale-returns.index')->with('success', 'Return removed.');
    }
}
