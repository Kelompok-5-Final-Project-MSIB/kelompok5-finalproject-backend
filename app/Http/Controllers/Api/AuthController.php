<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed'
        ]);
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => 'user'
        ]);
        
        Cart::create(['id_user' => $user->id_user]);
        
        return response()->json([
            'status' => 'OK',
            'code' => 200,
            'message' => 'User registered successfully!',
            'data' => $user
        ]);
    }

    public function addAdmin(Request $request)
    {
        $validasi = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed'
        ]);

        if($validasi->fails()) {
            return response()->json([
                'status' => 'Error',
                'message' => $request->errors()
            ], 400);
        }

        $prefix = 'admin';

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $prefix
        ]);

        return response()->json([
            'status' => 'OK',
            'code' => 200,
            'message' => 'Admin added successfully!',
            'data' => $user
        ]);
    }

    public function login(Request $request)
    {
        $validasi = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if($validasi->fails()) {
            return response()->json([
                'status' => 'Error',
                'message' => $validasi->errors()
            ], 400);
        }

        $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials);

        if(!$token) {
            return response()->json([
                'status' =>'Error',
                'message' => 'Email atau password salah. Silahkan cek kembali!'
            ], 401);
        }

        return response()->json([
            'status' => 'OK',
            'code' => 200,
            'message' => 'User logged in successfully!',
            'data' => [
                'user' => Auth::user(),
                'access_token' => $token,                
                'expires_in' => Auth::factory()->getTTL() * 60
            ]
        ]);
    }

    public function logout()
    {
        Auth::logout();

        return response()->json([
            'status' => 'OK',            
            'message' => 'User logged out successfully!'
        ]);
    }

    public function profile()
    {
        return response()->json([
            'status' => 'OK',
            'code' => 200,
            'message' => 'User data retrieved successfully!',
            'data' => Auth::user()
        ]);
    }

    public function refreshToken()
    {
        $token = Auth::refresh();

        return response()->json([
            'status' => 'OK',
            'code' => 200,
            'message' => 'Token refreshed successfully!',
            'data' => [
                'user' => Auth::user(),
                'access_token' => $token,
                'expires_in' => Auth::factory()->getTTL() * 60
            ]
        ]);
    }

    public function allUser(Request  $request)
    {
        $role = $request->query('role');

        $users = User::query();
        
        if ($role) {
            $users->where('role', $role);
        }

        $users = $users->get();

        if ($users->isEmpty()) {
            return response()->json([
                'status' => 'Error',
                'code' => 404,
                'message' => 'Users data not found!',
            ], 404);
        }

        return response()->json([
            'status' => 'OK',
            'code' => 200,
            'message' => 'Users retrieved successfully!',
            'data' => $users
        ]);
    }
}
