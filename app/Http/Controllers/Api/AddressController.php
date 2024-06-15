<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AddressController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->address) {
            return response()->json([
                'status' => 'error',
                'message' => 'User already has an address'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'id_province' => 'required|string',
            'province' => 'required|string',
            'id_city' => 'required|string',
            'city' => 'required|string',
            'zip_code' => 'required|string',
            'details' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        $client = new Client();
        $response = $client->request('POST', 'https://api.rajaongkir.com/starter/cost', [
            'headers' => [
                'key' => env('RAJA_ONGKIR_KEY'),
                'content-type' => 'application/x-www-form-urlencoded'
            ],
            'form_params' => [
                'origin' => 149,
                'destination' => $request->id_city,
                'weight' => 1000,
                'courier' => 'jne'
            ]
        ]);

        $shippingCost = json_decode($response->getBody()->getContents(), true)['rajaongkir']['results'][0]['costs'][0]['cost'][0]['value'];

        $address = Address::create([
            'id_address' => Str::uuid(),
            'id_user' => $user->id_user,
            'id_province' => $request->id_province,
            'province' => $request->province,
            'id_city' => $request->id_city,
            'city' => $request->city,
            'zip_code' => $request->zip_code,
            'details' => $request->details,
            'shipping_cost' => $shippingCost
        ]);

        return response()->json([
            'status' => 'OK',
            'code' => 200,
            'message' => 'Address created successfully',
            'data' => $address
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();

        $address = Address::where('id_address', $id)->first();

        if (!$address) {
            return response()->json([
                'status' => 'error',
                'message' => 'Address not found'
            ], 404);
        }

        if ($address->id_user !== $user->id_user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'id_province' => 'required|string',
            'province' => 'required|string',
            'id_city' => 'required|string',
            'city' => 'required|string',
            'zip_code' => 'required|string',
            'details' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        $shippingCost = $address->shipping_cost;

        if ($address->id_city !== $request->id_city) {
            $client = new Client();
            $response = $client->request('POST', 'https://api.rajaongkir.com/starter/cost', [
                'headers' => [
                    'key' => env('RAJA_ONGKIR_KEY'),
                    'content-type' => 'application/x-www-form-urlencoded'
                ],
                'form_params' => [
                    'origin' => 149,
                    'destination' => $request->id_city,
                    'weight' => 1000,
                    'courier' => 'jne'
                ]
            ]);

            $shippingCost = json_decode($response->getBody()->getContents(), true)['rajaongkir']['results'][0]['costs'][0]['cost'][0]['value'];
        }

        $address->update([
            'id_province' => $request->id_province,
            'province' => $request->province,
            'id_city' => $request->id_city,
            'city' => $request->city,
            'zip_code' => $request->zip_code,
            'details' => $request->details,
            'shipping_cost' => $shippingCost
        ]);

        return response()->json([
            'status' => 'OK',
            'code' => 200,
            'message' => 'Address updated successfully',
            'data' => $address
        ]);
    }

    public function show()
    {
        $user = Auth::user();

        $addresses = Address::where('id_user', $user->id_user)->first();

        return response()->json([
            'status' => 'OK',
            'code' => 200,
            'message' => 'Address retrieved successfully',
            'data' => $addresses
        ]);
    }

    public function getLocation(Request $request, $type)
    {
        $id = $request->query('id');
        $province = $type == 'city' ? $request->query('province') : null;

        $client = new Client();

        $response = $client->request('GET', "https://api.rajaongkir.com/starter/$type", [
            'headers' => [
                'key' => env('RAJA_ONGKIR_KEY'),
            ],
            'query' => [
                'id' => $id,
                'province' => $province
            ]
        ]);

        $cities = json_decode($response->getBody()->getContents());

        return response()->json([
            'status' => 'OK',
            'code' => 200,
            'message' => 'Cities retrieved successfully',
            'data' => $cities
        ]);
    }

    public function getProvinces(Request $request)
    {
        $id = $request->query('id');
        $province = $request->query('province');

        $client = new Client();

        $response = $client->request('GET', 'https://api.rajaongkir.com/starter/province', [
            'headers' => [
                'key' => env('RAJA_ONGKIR_KEY')
            ],
            'query' => [
                'id' => $id,
                'province' => $province
            ]
        ]);

        $provinces = json_decode($response->getBody()->getContents());

        return response()->json([
            'status' => 'OK',
            'code' => 200,
            'message' => 'Provinces retrieved successfully',
            'data' => $provinces
        ]);
    }
}
