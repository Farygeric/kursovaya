<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('login', $request->login)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $existingToken = ApiToken::where('user_id', $user->id)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if ($existingToken) {
            return response()->json([
                'token' => $existingToken->token,
                'user' => [
                    'id' => $user->id,
                    'login' => $user->login,
                    'role' => $user->role?->name,
                ],
                'message' => 'Existing token reused'
            ]);
        }

        $tokenValue = ApiToken::generateToken();
        $apiToken = ApiToken::create([
            'user_id' => $user->id,
            'token' => $tokenValue,
            'expires_at' => now()->addDays(30),
        ]);

        return response()->json([
            'token' => $tokenValue,
            'user' => [
                'id' => $user->id,
                'login' => $user->login,
                'role' => $user->role?->name,
            ],
            'message' => 'New token issued'
        ]);
    }

    public function logout(Request $request)
    {
        /** @var \App\Models\ApiToken|null $tokenRecord */
        $tokenRecord = $request->get('auth_token_record');

        if ($tokenRecord) {
            $tokenRecord->delete();
            return response()->json(['message' => 'Token revoked']);
        }

        return response()->json(['message' => 'No active token'], 400);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }
}