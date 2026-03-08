<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;



class AuthController extends Controller
{
    public function showLoginForm()
    {
        $token = request()->cookie('api_token');

        if ($token && PersonalAccessToken::findToken($token)?->tokenable) {
            return redirect()->route('dashboard');
        }

        return view('admin.auth.login');
    }

    public function logout(Request $request)
    {
        $token = $request->cookie('api_token');
        if ($token){
            $accessToken = PersonalAccessToken::findToken($token);
            $accessToken?->delete();
        }

        return redirect()->route('login')->withCookie(
            cookie()->forget('api_token')
        );
    }

}
