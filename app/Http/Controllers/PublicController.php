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

    public function updateCart(Request $request, $id)
    {
        $cart = session()->get('cart');
        $quantity = max(1, (int) $request->input('quantity', 1));
        
        if (isset($cart[$id])) {
            $cart[$id]['quantity'] = $quantity;
            session()->put('cart', $cart);
        }
        return redirect()->route('public.cart')->with('success', 'Cart updated successfully.');
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
        if ($request->filled('phone')) {
            $request->merge(['phone' => str_replace(' ', '', $request->phone)]);
        }

        $rules = [
            'customer_name'  => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'phone'          => ['required', 'string', new \App\Rules\EthiopianPhone],
            'address'        => 'required|string|max:1000',
            'notes'          => 'nullable|string|max:1000',
        ];

        if (! auth()->check()) {
            $rules['password']              = 'required|min:8|confirmed';
            $rules['password_confirmation'] = 'required';
        }

        $request->validate($rules);

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('public.shop')->with('error', 'Your cart is empty.');
        }

        $email = strtolower(trim($request->customer_email));

        DB::beginTransaction();
        try {
            // Handle User Authentication / Registration
            $userId = auth()->id();
            if (! $userId) {
                $user = \App\Models\User::where('email', $email)->first();
                if ($user) {
                    // Existing account: verify password to log in and protect account
                    if (! \Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
                        return redirect()->back()
                            ->withInput()
                            ->withErrors(['password' => 'An account with this email already exists. The password provided does not match. Please enter the correct password or log in first.']);
                    }
                    \Illuminate\Support\Facades\Auth::login($user);
                    $userId = $user->id;
                } else {
                    // Create new Buyer Account
                    $user = \App\Models\User::create([
                        'name'      => $request->customer_name,
                        'email'     => $email,
                        'phone'     => $request->phone,
                        'password'  => \Illuminate\Support\Facades\Hash::make($request->password),
                        'is_active' => true,
                    ]);

                    if (\Spatie\Permission\Models\Role::where('name', 'Client')->exists()) {
                        $user->assignRole('Client');
                    }

                    \Illuminate\Support\Facades\Auth::login($user);
                    $userId = $user->id;
                }

                // Associate any previous orders for this email
                Sale::where('customer_email', $email)
                    ->whereNull('client_id')
                    ->where('type', 'customer_order')
                    ->update(['client_id' => $userId]);
            }

            $total = 0;
            foreach ($cart as $item) {
                $total += $item['price'] * $item['quantity'];
            }

            $sale = Sale::create([
                'reference'           => 'ORD-' . strtoupper(uniqid()),
                'type'                => 'customer_order',
                'client_id'           => $userId,
                'storage_location_id' => \App\Models\StorageLocation::first()->id ?? 1,
                'customer_name'       => $request->customer_name,
                'customer_email'      => $email,
                'customer_phone'      => $request->phone,
                'customer_address'    => $request->address,
                'order_notes'         => $request->notes,
                'subtotal'            => $total,
                'total'               => $total,
                'paid_amount'         => 0,
                'payment_status'      => 'Unpaid',
                'status'              => 'Pending',
            ]);

            foreach ($cart as $id => $item) {
                SaleLine::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $id,
                    'quantity'   => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total'      => $item['price'] * $item['quantity'],
                ]);
            }

            session()->forget('cart');
            DB::commit();

            session()->put('last_order_reference', $sale->reference);
            session()->put('last_order_email', $email);
            session()->put('last_order_name', $request->customer_name);

            return redirect()->route('public.order.success');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error placing order: ' . $e->getMessage());
        }
    }

    public function orderSuccess()
    {
        $reference = session('last_order_reference');
        if (! $reference) {
            return redirect()->route('public.home');
        }
        $order = Sale::where('reference', $reference)->first();
        return view('public.order_success', compact('order'));
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


    public function submitReview(Request $request, \App\Models\Product $product)
    {
        $validated = $request->validate([
            'reviewer_name'  => 'required|string|max:255',
            'reviewer_email' => 'nullable|email|max:255',
            'rating'         => 'required|integer|min:1|max:5',
            'comment'        => 'nullable|string|max:2000',
            'order_reference'=> 'nullable|string|max:100',
        ]);

        \App\Models\ProductReview::create([
            'product_id'      => $product->id,
            'reviewer_name'   => $validated['reviewer_name'],
            'reviewer_email'  => $validated['reviewer_email'] ?? null,
            'rating'          => $validated['rating'],
            'comment'         => $validated['comment'] ?? null,
            'order_reference' => $validated['order_reference'] ?? null,
            'is_approved'     => true,
        ]);

        // Recalculate product rating
        $product->syncRating();

        return redirect()->back()->with('review_success', 'Thank you for your review!');
    }
}
