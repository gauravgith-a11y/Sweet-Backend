<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Delivery;
use Razorpay\Api\Api;
use App\Models\Cart;
use Exception;

class DeliveryController extends Controller
{
    // Create a new delivery
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|min:10|max:15',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'pincode' => 'required|string|min:6|max:6',
            'cart_items' => 'required|array',
            'total_amount' => 'required|numeric',
        ]);

        $delivery = Delivery::create([
            'user_id' => $validated['user_id'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'pincode' => $validated['pincode'],
            'total_amount' => $validated['total_amount'],
            'status' => 'pending',
        ]);

        // Save cart items
        foreach ($validated['cart_items'] as $item) {
            Cart::create([
                'user_id' => $validated['user_id'],
                'product_id' => $item['id'],
                'quantity' => $item['quantity'],
                'total_price' => $item['price'],
            ]);
        }

        return response()->json([
            'message' => 'Order created successfully',
            'delivery' => $delivery
        ], 201);
    }

    // Fetch all pending orders for a user
    public function pendingOrders(Request $request)
    {
        $userId = $request->query('user_id');

        if (!$userId) {
            return response()->json(['message' => 'User ID is required'], 400);
        }

        $deliveries = Delivery::where('user_id', $userId)
            ->where('status', 'pending')
            ->get();

        return response()->json(['deliveries' => $deliveries]);
    }

    // ✅ Step 1: Create Razorpay order
    public function createRazorpayOrder($deliveryId, Request $request)
    {
        try {
            $userId = $request->query('user_id');

            // Initialize Razorpay
            $api = new Api(env('RAZORPAY_KEY_ID'), env('RAZORPAY_KEY_SECRET'));

            // Create order on Razorpay
            $order = $api->order->create([
                'receipt' => 'order_rcptid_' . $deliveryId,
                'amount' => 50000, // ₹500 in paise
                'currency' => 'INR',
            ]);

            return response()->json([
                'success' => true,
                'order_id' => $order['id'],
                'amount' => $order['amount'],
                'currency' => $order['currency'],
                'key' => env('RAZORPAY_KEY_ID'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating Razorpay order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ✅ Step 2: Mark as paid after success
    public function payOrder($id, Request $request)
    {
        $userId = $request->query('user_id');

        $delivery = Delivery::where('id', $id)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->first();

        if (!$delivery) {
            return response()->json(['message' => 'Order not found or already paid'], 404);
        }

        $delivery->status = 'completed';
        $delivery->save();

        return response()->json(['message' => 'Payment successful', 'delivery' => $delivery]);
    }
}
