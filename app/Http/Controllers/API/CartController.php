<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = Cart::with(['items.product.images'])
        ->firstOrCreate(['user_id' => $request->user()->id]);
    
        return response()->json([
            'cart' => $cart,
            'total' => $cart->total
        ], 200);
    }

    public function addToCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'sometimes|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get or create cart for user_id
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);

        $product = Product::find($request->product_id);
        $quantity = $request->quantity ?? 1;

        // Check if product already in cart
        $existingItem = $cart->items()->where('product_id', $product->id)->first();

        if ($existingItem) {
            $existingItem->update(['quantity' => $existingItem->quantity + $quantity]);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $quantity
            ]);
        }

        return response()->json([
            'message' => 'Product added to cart',
            'cart' => $cart->load(['items.product.images']),
            'total' => $cart->total
        ], 201);
    }

    public function updateCartItem(Request $request, $itemId)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item = CartItem::find($itemId);
        if (!$item) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        $item->update(['quantity' => $request->quantity]);

        $cart = $item->cart->load(['items.product.images']);

        return response()->json([
            'message' => 'Cart item updated',
            'cart' => $cart,
            'total' => $cart->total
        ], 200);
    }

    public function removeFromCart($itemId)
    {
        $item = CartItem::find($itemId);
        if (!$item) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        $cart = $item->cart;
        $item->delete();

        return response()->json([
            'message' => 'Item removed from cart',
            'cart' => $cart->load(['items.product.images']),
            'total' => $cart->total
        ], 200);
    }

    public function clearCart(Request $request)
    {
        $cart = Cart::where('user_id', $request->user()->id)->first();
        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        $cart->items()->delete();

        return response()->json([
            'message' => 'Cart cleared',
            'cart' => $cart->load(['items.product.images']),
            'total' => $cart->total
        ], 200);
    }
}