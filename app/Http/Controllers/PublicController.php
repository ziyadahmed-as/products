<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Sale;
use App\Models\SaleLine;
use Illuminate\Support\Facades\DB;

class PublicController extends Controller
{
    public function index()
    {
        $featuredProducts = Product::with('category')
            ->where('is_active', true)
            ->where('is_featured', true)
            ->whereIn('type', ['resale_product', 'manufactured_product'])
            ->limit(5)
            ->get();

        $latestProducts = Product::with('category')
            ->where('is_active', true)
            ->whereIn('type', ['resale_product', 'manufactured_product'])
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();

        $topRatedProducts = Product::with('category')
            ->where('is_active', true)
            ->whereIn('type', ['resale_product', 'manufactured_product'])
            ->orderBy('rating', 'desc')
            ->limit(4)
            ->get();

        return view('public.home', compact('featuredProducts', 'latestProducts', 'topRatedProducts'));
    }

    public function about()
    {
        $topRatedProducts = Product::with('category')
            ->where('is_active', true)
            ->whereIn('type', ['resale_product', 'manufactured_product'])
            ->orderBy('rating', 'desc')
            ->limit(4)
            ->get();

        return view('public.about', compact('topRatedProducts'));
    }

    public function shop(Request $request)
    {
        $query = Product::with('category')
            ->where('is_active', true)
            ->whereIn('type', ['resale_product', 'manufactured_product']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'price_asc':
                    $query->orderBy('selling_price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('selling_price', 'desc');
                    break;
                case 'rating':
                    $query->orderBy('rating', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::where('is_active', true)->get();

        return view('public.shop', compact('products', 'categories'));
    }

    public function show(Product $product)
    {
        if (!$product->is_active || $product->type === 'raw_material') {
            abort(404);
        }

        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->whereIn('type', ['resale_product', 'manufactured_product'])
            ->limit(4)
            ->get();

        return view('public.show', compact('product', 'relatedProducts'));
    }

    public function cart()
    {
        $cart = session()->get('cart', []);
        return view('public.cart', compact('cart'));
    }

    public function addToCart(Request $request, Product $product)
    {
        $cart = session()->get('cart', []);
        $quantity = $request->input('quantity', 1);

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $quantity;
        } else {
            $cart[$product->id] = [
                'name' => $product->name,
                'price' => $product->selling_price,
                'quantity' => $quantity,
                'image' => $product->image,
                'sku' => $product->sku
            ];
        }

        session()->put('cart', $cart);
        return redirect()->back()->with('success', 'Product added to cart successfully!');
    }

    public function removeFromCart($id)
    {
        $cart = session()->get('cart');
        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }
        return redirect()->route('public.cart')->with('success', 'Product removed from cart.');
    }

    public function checkout()
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('public.shop')->with('error', 'Your cart is empty.');
        }
        return view('public.checkout', compact('cart'));
    }

    public function placeOrder(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
        ]);

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('public.shop')->with('error', 'Your cart is empty.');
        }

        DB::beginTransaction();
        try {
            $total = 0;
            foreach ($cart as $item) {
                $total += $item['price'] * $item['quantity'];
            }

            // Create Sale (Customer Order)
            $sale = Sale::create([
                'reference' => 'ORD-' . strtoupper(uniqid()),
                'type' => 'customer_order',
                'storage_location_id' => \App\Models\StorageLocation::first()->id ?? 1,
                'customer_name' => $request->customer_name . ' (' . $request->phone . ') - ' . $request->address,
                'subtotal' => $total,
                'total' => $total,
                'paid_amount' => 0,
                'payment_status' => 'Unpaid',
                'status' => 'Pending'
            ]);

            foreach ($cart as $id => $item) {
                SaleLine::create([
                    'sale_id' => $sale->id,
                    'product_id' => $id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total' => $item['price'] * $item['quantity'],
                ]);
            }

            session()->forget('cart');
            DB::commit();

            return redirect()->route('public.home')->with('success', 'Order placed successfully! We will contact you shortly.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error placing order: ' . $e->getMessage());
        }
    }

    public function contactSend(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        return redirect()->back()->with('status', 'message-sent');
    }
}
