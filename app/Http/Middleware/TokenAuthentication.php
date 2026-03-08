<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class TokenAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie('api_token');

        if (! $token) {
            return redirect()->route('login');
        }

        $accessToken = PersonalAccessToken::findToken($token);
        if (! $accessToken) {
            return redirect()->route('login')->withCookie(
                cookie()->forget('api_token')
            );
        }

        $user = $accessToken->tokenable;

        if (! $user) {
            return redirect()->route('login')->withCookie(
                cookie()->forget('api_token')
            );
        }

        auth()->setUser($user);

        return $next($request);

    }
}
