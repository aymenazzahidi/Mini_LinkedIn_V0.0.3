<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // POST /api/register
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role'     => 'required|in:candidat,recruteur',
        ]);

        $user  = User::create($data);
        $token = auth()->login($user);

        return $this->respondWithToken($token, $user, 201);
    }

    // POST /api/login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['message' => 'Identifiants invalides.'], 401);
        }

        return $this->respondWithToken($token, auth()->user());
    }

    // POST /api/logout
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Déconnexion réussie.']);
    }

    // POST /api/refresh
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh(), auth()->user());
    }

    // GET /api/me
    public function me()
    {
        return response()->json(auth()->user());
    }

    private function respondWithToken(string $token, User $user, int $status = 200)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60,
            'user'         => $user,
        ], $status);
    }
}