<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'code' => 422,
                'message' => 'Validasi gagal',
                'errors' => $validate->errors()
            ], 422);
        }

        try {
            // Cek kredensial
            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    'code' => 401,
                    'message' => 'Email atau password salah',
                    'errors' => 'Unauthorized'
                ], 401);
            }

            // Ambil user beserta data role-nya
            $user = User::with('role')->where('email', $request->email)->firstOrFail();

            // Generate Sanctum Token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'code' => 200,
                'message' => 'Login berhasil',
                'data' => [
                    'user' => $user,
                    'access_token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 200);

        } catch (\Exception $e) {
            $message = $e->getMessage() . " | " . $e->getFile() . " | " . $e->getLine();
            Log::critical($message);

            return response()->json([
                'code' => 500,
                'message' => 'Terjadi kesalahan pada server',
                'errors' => $message
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            // Hapus token yang sedang digunakan
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'code' => 200,
                'message' => 'Logout berhasil',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            $message = $e->getMessage() . " | " . $e->getFile() . " | " . $e->getLine();
            Log::critical($message);

            return response()->json([
                'code' => 500,
                'message' => 'Gagal logout',
                'errors' => $message
            ], 500);
        }
    }
}
