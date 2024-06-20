<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function index()
    {
        $profiles = Profile::all();
        return response()->json([
            'status' => 'OK',
            'code' => 200,
            'message' => 'Profiles retrieved successfully!',
            'data' => $profiles
        ], 200);
    }

    public function show($id)
    {
        $profile = Profile::find($id);

        if (is_null($profile)) {
            return response()->json([
                'status' => 'Error',
                'code' => 404,
                'message' => 'Profile not found!',
            ], 404);
        }

        return response()->json([
            'status' => 'OK',
            'code' => 200,
            'message' => 'Profile retrieved successfully!',
            'data' => $profile
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:profiles',
            'bio' => 'nullable|string',
            'avatar' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'Error',
                'code' => 400,
                'message' => $validator->errors()
            ], 400);
        }

        $profile = Profile::create($request->all());

        return response()->json([
            'status' => 'OK',
            'code' => 201,
            'message' => 'Profile created successfully!',
            'data' => $profile
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $profile = Profile::find($id);

        if (is_null($profile)) {
            return response()->json([
                'status' => 'Error',
                'code' => 404,
                'message' => 'Profile not found!',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:profiles,email,' . $id,
            'bio' => 'nullable|string',
            'avatar' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'Error',
                'code' => 400,
                'message' => $validator->errors()
            ], 400);
        }

        $profile->update($request->all());

        return response()->json([
            'status' => 'OK',
            'code' => 200,
            'message' => 'Profile updated successfully!',
            'data' => $profile
        ], 200);
    }

    public function destroy($id)
    {
        $profile = Profile::find($id);

        if (is_null($profile)) {
            return response()->json([
                'status' => 'Error',
                'code' => 404,
                'message' => 'Profile not found!',
            ], 404);
        }

        $profile->delete();

        return response()->json([
            'status' => 'OK',
            'code' => 204,
            'message' => 'Profile deleted successfully!',
        ], 204);
    }
}
