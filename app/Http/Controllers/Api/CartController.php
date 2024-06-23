<?php

namespace App\Http\Controllers\Api;


use App\Models\Cart;
use App\Http\Controllers\Controller;
use App\Models\CartDetails;
use App\Models\Product;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

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

    public function addProduct($id_product)
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

        $productSold = Product::find($id_product);

        if ($productSold->status == 'sold out') {
            return response()->json([
                'status' => 'error',
                'message' => 'Product is sold out'
            ], 400);
        }

        // Check if the product is already in the cart
        $productExists = CartDetails::where('id_cart', $cart->id_cart)
            ->where('id_product', $id_product)
            ->exists();

        if ($productExists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product is already in the cart'
            ], 400);
        }

        // Add the product to the cart
        $cartDetail = CartDetails::create([
            'id_cart' => $cart->id_cart,
            'id_product' => $id_product,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $cartDetail
        ]);
    }

    public function getCartProducts()
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

        // Get all cart details for the cart
        $cartDetails = CartDetails::where('id_cart', $cart->id_cart)->get();

        // Get all products for the cart details
        $products = $cartDetails->map(function ($cartDetail) {
            $product = Product::find($cartDetail->id_product);
            $product = $this->imageService->convertImage($product);
            $discountedPrice = round($product->price - ($product->price * $product->discount / 100));
            return [
                'id_product' => $product->id_product,
                'name_product' => $product->name_product,
                'price' => $product->price,
                'discount' => $product->discount,
                'discounted_price' => $discountedPrice,
                'desc' => $product->desc,
                'image' => $product->image,
            ];
        });

        $totalPrice = round($products->sum('discounted_price'));

        return response()->json([
            'status' => 'success',
            'data' => [
                'data' => $products,
                'total' => $totalPrice
            ]
        ]);
    }

    public function deleteProduct($id_product)
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

        // Check if the product is in the cart
        $cartDetail = CartDetails::where('id_cart', $cart->id_cart)
            ->where('id_product', $id_product)
            ->first();

        if (!$cartDetail) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found in the cart'
            ], 404);
        }

        // Delete the product from the cart
        $cartDetail->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Product removed from the cart'
        ]);
    }
}
