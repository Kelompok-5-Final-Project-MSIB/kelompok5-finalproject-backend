<?php

namespace App\Http\Controllers\Api;


use App\Models\Cart;
use App\Http\Controllers\Controller;
use App\Models\CartDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function create()
    {
        $user = Auth::user();

        // Check if the user already has a cart
        $cart = Cart::where('id_user', $user->id_user)->first();

        if ($cart) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cart already exists'
            ], 400);
        }

        // Create a new cart for the user
        $cart = Cart::create(['id_user' => $user->id_user]);

        return response()->json([
            'status' => 'success',
            'data' => $cart
        ]);
    }

    public function addProduct(Request $request)
    {
        $user = Auth::user();

        // Get the user's cart
        $cart = Cart::where('id_user', $user->id_user)->first();

        if (!$cart) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cart not found'
            ], 404);
        }

        // Validate the request
        $request->validate([
            'id_product' => 'required',            
        ]);

        // Add the product to the cart
        $cartDetail = CartDetails::create([
            'id_cart' => $cart->id_cart,
            'id_product' => $request->id_product,            
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $cartDetail
        ]);
    }
}
