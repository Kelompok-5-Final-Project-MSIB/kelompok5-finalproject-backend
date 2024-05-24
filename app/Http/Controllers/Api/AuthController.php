<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

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

    public function allUser()
    {
        $users = User::all();

        return response()->json([
            'status' => 'OK',
            'code' => 200,
            'message' => 'Users retrieved successfully!',
            'data' => $users
        ]);
    }
}
