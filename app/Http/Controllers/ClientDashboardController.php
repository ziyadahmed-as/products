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
}
