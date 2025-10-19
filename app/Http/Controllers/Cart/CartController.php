<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    //  Get all cart items
    public function index()
    {
        return Cart::with('product')->get();
    }

    // Add to cart
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::find($request->product_id);
        $totalPrice = $product->price_per_kg * $request->quantity; // Assuming 1 quantity = 1kg

        $cart = Cart::create([
            'user_id' => $request->user_id ?? null,
            'product_id' => $product->id,
            'quantity' => $request->quantity,
            'total_price' => $totalPrice,
        ]);

        return response()->json(['message' => 'Added to cart', 'cart' => $cart], 201);
    }

    // Remove from cart
    public function destroy($id)
    {
        $cart = Cart::findOrFail($id);
        $cart->delete();

        return response()->json(['message' => 'Item removed']);
    }
}
