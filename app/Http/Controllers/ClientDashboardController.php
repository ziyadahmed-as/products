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
        return view('public.client.dashboard');
    }

    public function orders()
    {
        $orders = Sale::where('client_id', Auth::id())
            ->where('type', 'customer_order')
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

        // 4. One rating per product per order check
        $existingReview = ProductReview::where('product_id', $product->id)
            ->where('order_reference', $order->reference)
            // Assuming reviewer_email could track user, or add a user_id to reviews if needed.
            // But order_reference + product_id is unique enough for this order.
            ->first();

        if ($existingReview) {
            return redirect()->back()->with('error', 'You have already reviewed this product for this order.');
        }

        $validated = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        ProductReview::create([
            'product_id'      => $product->id,
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
        // Assuming review ownership by email for now, since ProductReview doesn't explicitly have user_id
        // But we can just use reviewer_email to fetch the authenticated user's reviews
        $reviews = ProductReview::where('reviewer_email', Auth::user()->email)
            ->with('product')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('public.client.reviews', compact('reviews'));
    }
}
