<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Support\Google2FAAuthenticator;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FALaravel\Support\Authenticator;

class TwoFactorAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // $authenticator = app(Google2FAAuthenticator::class)->boot($request);

        // if ($authenticator->isAuthenticated()) {
        //     if(Auth::user()->loginSecurity()->exists()) return $next($request);

        //     return response()->json([
        //         'error' => true,
        //         '2fa' => false,
        //         'message' => 'Auenticação em 2 fatores não realizada'
        //     ], 401);
        // }

        // return $authenticator->makeRequestOneTimePasswordResponse();
        $user = Auth::user();
        $lastUsedSecurity = Carbon::parse($user->loginSecurity()->first()->last_used);
        $lastCreateAccessToken = Carbon::parse($user->tokens()->first()->created_at);

        if($user->loginSecurity()->exists()){
            if($lastCreateAccessToken < $lastUsedSecurity){
                return $next($request);
            }else{
                return response()->json([
                    'error' => true,
                    '2fa' => false,
                    'message' => 'Auenticação em 2 fatores não realizada'
                ], 401);
            }
        }

        return response()->json([
            'error' => true,
            '2fa' => false,
            'message' => 'Auenticação em 2 fatores não cadastrada'
        ], 401);

    }
}
