<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function initiateCheckout(Request $request)
    {
        $user = $request->user();
        $cart = Cart::with(['items.product'])->where('user_id', $user->id)->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty'], 400);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $lineItems = [];
            foreach ($cart->items as $item) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $item->product->name,
                        ],
                        'unit_amount' => $item->product->price * 100, // Amount in cents
                    ],
                    'quantity' => $item->quantity,
                ];
            }

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => config('app.frontend_url') . '/checkout/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => config('app.frontend_url') . '/checkout/canceled',
                'metadata' => [
                    'user_id' => $user->id,
                    'cart_id' => $cart->id,
                ],
            ]);

            return response()->json(['session_id' => $session->id, 'url' => $session->url]);

        } catch (ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sigHeader, $endpointSecret
            );
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            
            $this->createOrderFromSession($session);
        }

        return response()->json(['status' => 'success']);
    }

    protected function createOrderFromSession($session)
    {
        $user = User::find($session->metadata->user_id);
        $cart = Cart::with('items.product')->find($session->metadata->cart_id);

        if (!$user || !$cart) {
            return;
        }

        // Create order
        $order = Order::create([
            'user_id' => $user->id,
            'total_amount' => $session->amount_total / 100, // Convert from cents
            'status' => 'paid',
            'payment_method' => 'stripe',
            'transaction_id' => $session->payment_intent,
        ]);

        // Create order items
        foreach ($cart->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->product->price,
            ]);
        }

        // Clear the cart
        $cart->items()->delete();

    }

    public function checkoutSuccess(Request $request)
    {
        $sessionId = $request->query('session_id');
        
        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = Session::retrieve($sessionId);

            if ($session->payment_status === 'paid') {
                $order = Order::where('transaction_id', $session->payment_intent)->first();
                
                return response()->json([
                    'message' => 'Payment successful',
                    'order' => $order,
                ]);
            }

            return response()->json(['message' => 'Payment not completed'], 400);

        } catch (ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

/*************  ✨ Windsurf Command ⭐  *************/
    /**
*******  fef73aaf-ac55-4eab-9222-0c8a1d39c429  *******/    
protected function createOrder(Request $request)
    {
        //dd($request->all());
        $user = User::find($request->user()->id);
        DB::enableQueryLog();
        $cart = Cart::with('items.product')->find($request->cart_id);
        //dd(DB::getQueryLog());

        if (!$user || !$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        // Create order
        $order = Order::create([
            'user_id' => $user->id,
            'total_amount' => $request->total_amount, // Convert from cents
            'status' => 'completed',
            'payment_method' => 'cash',
            'transaction_id' => 123,
        ]);
        //dd($cart->items);
        // Create order items
        $quantity = 0;
        $price = 0;
        foreach ($cart->items as $item) {
            /* $quantity = $item->quantity;
            $price = $item->product->price; */
            //dd("testset");
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->product->price,
            ]);
        }

        // Clear the cart
        //$cart->items()->delete();

        return response()->json([
            'message' => 'Order Confirmed',
            'quantity' => $quantity,
            'total' => $price
        ], 200);
    }
}