<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
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

    public function createRazorpayOrder($deliveryId, Request $request)
    {
        try {
            $userId = $request->query('user_id');

            // Get Razorpay keys from .env
            $key = env('RAZORPAY_KEY');
            $secret = env('RAZORPAY_SECRET');

            if (!$key || !$secret) {
                \Log::error('Razorpay keys missing in .env');
                return response()->json([
                    'success' => false,
                    'message' => 'Razorpay keys are not configured on the server.',
                ], 500);
            }

            // Find delivery and validate
            $delivery = Delivery::where('id', $deliveryId)
                ->where('user_id', $userId)
                ->where('status', 'pending')
                ->first();

            if (!$delivery) {
                return response()->json([
                    'success' => false,
                    'message' => 'Delivery not found or not payable'
                ], 404);
            }

            // Validate amount
            if (!isset($delivery->total_amount) || !is_numeric($delivery->total_amount) || $delivery->total_amount <= 0) {
                \Log::error("Invalid delivery amount", ['delivery_id' => $deliveryId, 'amount' => $delivery->total_amount]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid order amount. Please contact support.'
                ], 400);
            }

            // Initialize Razorpay API
            $api = new \Razorpay\Api\Api($key, $secret);

            // Convert amount to paise
            $amountPaise = intval(round($delivery->total_amount * 100));

            // Create order
            $order = $api->order->create([
                'receipt' => 'order_rcptid_' . $deliveryId,
                'amount' => $amountPaise,
                'currency' => 'INR',
            ]);

            // Success response
            return response()->json([
                'success' => true,
                'order_id' => $order['id'],
                'amount' => $order['amount'],
                'currency' => $order['currency'],
                'key' => $key,
            ]);
        } catch (\Exception $e) {
            \Log::error('Razorpay order creation failed', [
                'delivery_id' => $deliveryId,
                'user_id' => $request->query('user_id'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error creating Razorpay order',
                'error' => $e->getMessage(), // remove in production
            ], 500);
        }
    }

    public function payOrder($id, Request $request)
    {
        $userId = $request->query('user_id');

        $delivery = Delivery::where('id', $id)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->first();

        if (!$delivery) {
            return response()->json(['message' => 'Order not found or already processed'], 404);
        }

        // ✅ Update delivery status to success
        $delivery->status = 'success';
        $delivery->save();

        return response()->json([
            'success' => true,
            'message' => 'Payment successful and delivery updated to success',
            'delivery' => $delivery
        ]);
    }
}
