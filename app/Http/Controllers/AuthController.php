<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService) {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request) {
        $user = $this->authService->register($request->validated());
        return response()->json([
            'status' => 'success',
            'user' => new UserResource($user)
        ], 201);
    }

    public function login(LoginRequest $request) {
        $result = $this->authService->login($request->validated());
        return response()->json([
            'status' => 'success',
            'token' => $result['token'],
            'user' => new UserResource($result['user'])
        ]);
    }

    public function logout(Request $request)
    {
        // Menghapus token yang sedang digunakan saat ini
        $request->user()->currentAccessToken()->delete();
    
        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil logout, token telah dihapus.'
        ], 200);
    }
}