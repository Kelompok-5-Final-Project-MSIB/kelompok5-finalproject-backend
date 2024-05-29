<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AddressController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'province' => 'required|string',
            'city' => 'required|string',
            'zip_code' => 'required|string',
            'details' => 'required|string',
            'shipping_cost' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        $address = Address::create([
            'id_address' => Str::uuid(),
            'id_user' => $user->id_user,
            'province' => $request->province,
            'city' => $request->city,
            'zip_code' => $request->zip_code,
            'details' => $request->details,
            'shipping_cost' => $request->shipping_cost
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
            'province' => 'required|string',
            'city' => 'required|string',
            'zip_code' => 'required|string',
            'details' => 'required|string',
            'shipping_cost' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        $address->update([
            'province' => $request->province,
            'city' => $request->city,
            'zip_code' => $request->zip_code,
            'details' => $request->details,
            'shipping_cost' => $request->shipping_cost
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

        $addresses = Address::where('id_user', $user->id_user)->get();

        return response()->json([
            'status' => 'OK',
            'code' => 200,
            'message' => 'Address retrieved successfully',
            'data' => $addresses
        ]);
    }
}
