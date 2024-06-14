<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartDetails;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Midtrans\Notification;
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

        // $transaction = Transaction::create([
        //     'order_id' => 'PAYMENT-' . rand(1000, 9999),
        //     'status' => 'pending',
        //     'total' => $total,
        //     'customer_name' => $user->name,
        //     'customer_email' => $user->email,
        // ]);

        $params = [
            'transaction_details' => [
                'order_id' => 'PAYMENT-' . rand(1000, 9999),
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

    public function handleNotification()
    {
        $notif = new Notification();

        $transaction = $notif->transaction_status;
        $order_id = $notif->order_id;

        $transactionData = Transaction::where('order_id', $order_id)->first();

        if (!$transactionData) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaction not found'
            ], 404);
        }

        if ($transaction == 'capture' || $transaction == 'settlement') {
            $transactionData->status = 'success';
        } else if ($transaction == 'pending') {
            $transactionData->status = 'pending';
        } else if ($transaction == 'deny' || $transaction == 'cancel' || $transaction == 'expire') {
            $transactionData->status = 'failure';
        }

        $transactionData->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Transaction status updated'
        ]);
    }
}
