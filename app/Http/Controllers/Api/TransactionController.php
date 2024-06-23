<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartDetails;
use App\Models\Product;
use App\Models\Transaction;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Illuminate\Support\Str;
use Midtrans\Snap;

class TransactionController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$clientKey = env('MIDTRAANS_CLIENT_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION');
        Config::$isSanitized = env('MIDTRANS_IS_SANITIZED');
        Config::$is3ds = env('MIDTRANS_IS_3DS');
    }

    public function payment()
    {
        $user = Auth::user();
        
        $address = $user->address;
        
        if (!$address) {
            return response()->json([
                'status' => 'error',
                'message' => 'Address not found'
            ], 404);
        }
        
        $cartController = app(CartController::class);
        $cartProducts = $cartController->getCartProducts()->getData();

        if ($cartProducts->status == 'error') {
            return response()->json($cartProducts, 404);
        }

        $item_details = [];
        $total = $cartProducts->data->total + $address->shipping_cost;

        foreach ($cartProducts->data->data as $product) {
            $item_detail = [
                'id' => $product->id_product,
                'name' => $product->name_product,
                'price' => $product->discounted_price,
                'quantity' => 1, // You may need to adjust this based on your application
            ];


            array_push($item_details, $item_detail);
        }

        $order_id = 'PAYMENT-' . rand(1000, 9999);

        $transaction = Transaction::create([
            'id_transaction' => Str::uuid(),
            'order_id' => $order_id,
            'id_user' => $user->id_user,
            'gross_amount' => $total,
            'transaction_status' => 'pending',
            'fraud_status' => 'accept',            
        ]);

        $params = [
            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => $total,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
            'item_details' => $item_details,
        ];


        $snapToken = Snap::getSnapToken($params);
        $redirectUrl = Snap::createTransaction($params)->redirect_url;

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Transaction created successfully',
            'data' => $params,
            'snap_token' => $snapToken,
            'redirect_url' => $redirectUrl,
        ]);
    }

    public function paymentByIdProduct($id_product, Request $request)
    {
        $user = Auth::user();
        
        $address = $user->address;
        
        if (!$address) {
            return response()->json([
                'status' => 'error',
                'message' => 'Address not found'
            ], 404);
        }
        
        $product = Product::find($id_product);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found'
            ], 404);
        }

        $item_details = [];
        $discountedPrice = round($product->price - ($product->price * $product->discount / 100));
        $total = $discountedPrice + $address->shipping_cost;

        $item_detail = [
            'id' => $product->id_product,
            'name' => $product->name_product,
            'price' => $total,
            'quantity' => 1, // You may need to adjust this based on your application
        ];

        array_push($item_details, $item_detail);
        $order_id = 'PAYMENT-' . rand(1000, 9999);

        $transaction = Transaction::create([
            'id_transaction' => Str::uuid(),
            'order_id' => $order_id,
            'id_user' => $user->id_user,
            'gross_amount' => $total,
            'transaction_status' => 'pending',
            'fraud_status' => 'accept',            
        ]);

        $params = [
            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => $total,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
            'item_details' => $item_details,
        ];

        $snapToken = Snap::getSnapToken($params);
        $redirectUrl = Snap::createTransaction($params)->redirect_url;

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Transaction created successfully',
            'data' => $params,
            'snap_token' => $snapToken,
            'redirect_url' => $redirectUrl,
        ]);
    }

    public function callback($order_id)
    {
        $order_id = $order_id;

        $client = new Client();
        $response = $client->request('GET', 'https://api.sandbox.midtrans.com/v2/' . $order_id . '/status', [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic U0ItTWlkLXNlcnZlci12bkhWTU5fUkd4a2J0Y0NuMXdDQ1k1U1Q6:'
            ]
        ]);

        $body = json_decode($response->getBody()->getContents());

        if ($body->transaction_status == 'settlement') {
            $transaction = Transaction::where('order_id', $order_id)->first();
            $transaction->transaction_status = $body->transaction_status;
            $transaction->save();

            $cart = Cart::where('id_user', $transaction->id_user)->first();
            $cartDetails = CartDetails::where('id_cart', $cart->id_cart)->get();
            foreach ($cartDetails as $cartDetail) {
                $product = Product::find($cartDetail->id_product);
                $product->status = 'sold out';
                $product->save();

                $cartDetail->delete();
            }

            $product->status = 'sold out';
            $product->save();
        }

        return response()->json($body);
    }

    public function callbackByProduct($order_id)
    {
        $order_id = $order_id;

        $client = new Client();
        $response = $client->request('GET', 'https://api.sandbox.midtrans.com/v2/' . $order_id . '/status', [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic U0ItTWlkLXNlcnZlci12bkhWTU5fUkd4a2J0Y0NuMXdDQ1k1U1Q6:'
            ]
        ]);

        $body = json_decode($response->getBody()->getContents());

        if ($body->transaction_status == 'settlement') {
            $transaction = Transaction::where('order_id', $order_id)->first();
            $transaction->transaction_status = $body->transaction_status;
            $transaction->save();

            $product = Product::find($transaction->id_product);
            $product->status = 'sold out';
            $product->save();
        }

        return response()->json($body);
    }
}
