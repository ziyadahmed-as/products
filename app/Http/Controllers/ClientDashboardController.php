<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Sale;
use App\Models\SaleLine;
use App\Models\Product;
use App\Models\ProductReview;

class ClientDashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $recentOrders = Sale::where('client_id', $userId)
            ->where('type', 'customer_order')
            ->with(['lines.product'])
            ->latest()
            ->limit(5)
            ->get();

        $totalOrders   = Sale::where('client_id', $userId)->where('type', 'customer_order')->count();
        $totalSpent    = Sale::where('client_id', $userId)->where('type', 'customer_order')->sum('total');
        $pendingOrders = Sale::where('client_id', $userId)->where('type', 'customer_order')->where('status', 'Pending')->count();
        $myReviews     = ProductReview::where(function ($q) {
            $q->where('user_id', Auth::id())
              ->orWhere('reviewer_email', Auth::user()->email);
        })->count();

        return view('public.client.dashboard', compact('recentOrders', 'totalOrders', 'totalSpent', 'pendingOrders', 'myReviews'));
    }

    public function orders()
    {
        $orders = Sale::where('client_id', Auth::id())
            ->where('type', 'customer_order')
            ->with(['lines.product'])
            ->withCount('lines')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('public.client.orders', compact('orders'));
    }

    public function showOrder(Sale $order)
    {
        // Security check
        if ($order->client_id !== Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

        $order->load(['lines.product']);

        return view('public.client.order_show', compact('order'));
    }

    public function submitRating(Request $request, Sale $order, Product $product)
    {
        // 1. Authenticated client check (handled by auth middleware)
        $userId = Auth::id();

        // 2. Order belongs to client check
        if ($order->client_id !== $userId) {
            abort(403, 'Unauthorized access.');
        }

        // 3. Product belongs to order check
        $hasPurchased = $order->lines()->where('product_id', $product->id)->exists();
        if (!$hasPurchased) {
            abort(403, 'You did not purchase this product in this order.');
        }

        $validated = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $existingReview = ProductReview::where('product_id', $product->id)
            ->where('order_reference', $order->reference)
            ->first();

        if ($existingReview) {
            $existingReview->update([
                'rating'  => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ]);
            $product->syncRating();
            return redirect()->back()->with('success', 'Your review for ' . $product->name . ' has been updated.');
        }

        ProductReview::create([
            'product_id'      => $product->id,
            'user_id'         => $userId,
            'reviewer_name'   => Auth::user()->name,
            'reviewer_email'  => Auth::user()->email,
            'rating'          => $validated['rating'],
            'comment'         => $validated['comment'] ?? null,
            'order_reference' => $order->reference,
            'is_approved'     => true,
        ]);

        $product->syncRating();

        return redirect()->back()->with('success', 'Thank you! Your review for ' . $product->name . ' has been submitted.');
    }

    public function reviews()
    {
        $reviews = ProductReview::where(function ($q) {
                $q->where('user_id', Auth::id())
                  ->orWhere('reviewer_email', Auth::user()->email);
            })
            ->with('product')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('public.client.reviews', compact('reviews'));
    }

    public function profile()
    {
        return view('public.client.profile', ['user' => Auth::user()]);
    }

    public function updateProfile(\Illuminate\Http\Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name'         => 'required|string|max:255',
            'phone'        => 'nullable|string|max:30',
            'current_password'      => 'nullable|string',
            'password'              => 'nullable|min:8|confirmed|required_with:current_password',
            'password_confirmation' => 'nullable',
        ]);

        if ($request->filled('current_password')) {
            if (! \Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
        }

        $user->name  = $request->name;
        $user->phone = $request->phone;
        if ($request->filled('password')) {
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        }
        $user->save();

        return back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Client Personal Spending & Order Reports
     */
    public function reports(Request $request)
    {
        $userId = Auth::id();
        $query = Sale::where('client_id', $userId)
            ->where('type', 'customer_order')
            ->with(['lines.product'])
            ->latest('id');

        // Date Period filter
        $period = $request->input('period');
        if ($period === 'today') {
            $query->whereDate('created_at', \Carbon\Carbon::today());
        } elseif ($period === 'this_week') {
            $query->whereBetween('created_at', [\Carbon\Carbon::now()->startOfWeek(), \Carbon\Carbon::now()->endOfWeek()]);
        } elseif ($period === 'this_month') {
            $query->whereBetween('created_at', [\Carbon\Carbon::now()->startOfMonth(), \Carbon\Carbon::now()->endOfMonth()]);
        } elseif ($period === 'this_year') {
            $query->whereBetween('created_at', [\Carbon\Carbon::now()->startOfYear(), \Carbon\Carbon::now()->endOfYear()]);
        } elseif ($request->filled('from') || $request->filled('to')) {
            if ($request->filled('from')) {
                $query->where('created_at', '>=', \Carbon\Carbon::parse($request->from)->startOfDay());
            }
            if ($request->filled('to')) {
                $query->where('created_at', '<=', \Carbon\Carbon::parse($request->to)->endOfDay());
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('reference', 'like', $term)
                  ->orWhereHas('lines.product', fn ($p) => $p->where('name', 'like', $term));
            });
        }

        // Summary calculations
        $totalOrdersCount = (clone $query)->count();
        $totalSpent       = (clone $query)->sum('total');
        $totalPaid        = (clone $query)->sum('paid_amount');
        $totalOutstanding = $totalSpent - $totalPaid;
        $completedCount   = (clone $query)->where('status', 'Completed')->count();

        $orders = $query->paginate(15)->withQueryString();

        return view('public.client.reports', compact(
            'orders',
            'totalOrdersCount',
            'totalSpent',
            'totalPaid',
            'totalOutstanding',
            'completedCount'
        ));
    }

    /**
     * Export Client personal order report to CSV
     */
    public function exportOrders(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $userId = Auth::id();
        $user = Auth::user();

        \App\Services\ActivityLogger::log(
            module: 'Reports',
            actionType: 'export',
            description: "Client {$user->name} exported personal order report to CSV.",
            properties: ['format' => 'CSV']
        );

        $query = Sale::where('client_id', $userId)
            ->where('type', 'customer_order')
            ->with(['lines.product'])
            ->latest('id');

        // Apply same filters
        $period = $request->input('period');
        if ($period === 'today') {
            $query->whereDate('created_at', \Carbon\Carbon::today());
        } elseif ($period === 'this_week') {
            $query->whereBetween('created_at', [\Carbon\Carbon::now()->startOfWeek(), \Carbon\Carbon::now()->endOfWeek()]);
        } elseif ($period === 'this_month') {
            $query->whereBetween('created_at', [\Carbon\Carbon::now()->startOfMonth(), \Carbon\Carbon::now()->endOfMonth()]);
        } elseif ($period === 'this_year') {
            $query->whereBetween('created_at', [\Carbon\Carbon::now()->startOfYear(), \Carbon\Carbon::now()->endOfYear()]);
        } elseif ($request->filled('from') || $request->filled('to')) {
            if ($request->filled('from')) {
                $query->where('created_at', '>=', \Carbon\Carbon::parse($request->from)->startOfDay());
            }
            if ($request->filled('to')) {
                $query->where('created_at', '<=', \Carbon\Carbon::parse($request->to)->endOfDay());
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $filename = 'my_orders_report_' . date('Y_m_d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'Order Reference',
                'Order Date',
                'Products & Quantities',
                'Subtotal (ETB)',
                'Discount (ETB)',
                'Tax (ETB)',
                'Total (ETB)',
                'Amount Paid (ETB)',
                'Outstanding Balance (ETB)',
                'Payment Status',
                'Order Status',
            ]);

            $query->chunk(100, function ($rows) use ($handle) {
                foreach ($rows as $ord) {
                    $itemsSummary = $ord->lines->map(fn ($l) => ($l->product?->name ?? 'Item') . ' (' . number_format($l->quantity, 2) . ' @ Br' . number_format($l->unit_price, 2) . ')')->implode('; ');

                    fputcsv($handle, [
                        $ord->reference,
                        $ord->created_at ? $ord->created_at->format('Y-m-d H:i') : '',
                        $itemsSummary,
                        number_format($ord->subtotal, 2, '.', ''),
                        number_format($ord->discount, 2, '.', ''),
                        number_format($ord->tax, 2, '.', ''),
                        number_format($ord->total, 2, '.', ''),
                        number_format($ord->paid_amount, 2, '.', ''),
                        number_format($ord->total - $ord->paid_amount, 2, '.', ''),
                        ucfirst($ord->payment_status),
                        ucfirst($ord->status),
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}
