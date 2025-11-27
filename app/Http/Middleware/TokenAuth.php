<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TokenAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Unauthorized: missing token'], 401);
        }

        $apiToken = ApiToken::where('token', $token)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$apiToken) {
            return response()->json(['error' => 'Unauthorized: invalid or expired token'], 401);
        }

        Auth::login($apiToken->user); 
        $request->attributes->add(['auth_token_record' => $apiToken]);

        return $next($request);
    }
}