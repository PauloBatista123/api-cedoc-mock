<?php

namespace App\Http\Controllers;

use App\Models\LoginSecurity;
use App\Support\Google2FAAuthenticator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Google2FA;
use PragmaRX\Google2FALaravel\Support\Authenticator;

class LoginSecurityController extends Controller
{
    /**
     * Show 2FA Setting form
     */
    public function show2faForm(Request $request)
    {
        $user = Auth::user();
        $google2fa_url = "";
        $secret_key = "";

        if($user->loginSecurity()->exists()){
            $google2fa_url = Google2FA::getQRCodeInline(
                'CoopDocs',
                $user->name,
                $user->loginSecurity->google2fa_secret
            );

            $secret_key = $user->loginSecurity->google2fa_secret;
        }

        return response()->json([
            'error' => false,
            'message' => 'Autenticação em dois fatores',
            'user' => $user,
            'secret' => $secret_key,
            'google2fa_url' => $google2fa_url,
        ], 200);
    }

    /**
     * Generate 2FA secret key
     */
    public function generate2faSecret(Request $request)
    {
        $user = Auth::user();

        // Add the secret key to the registration data
        $login_security = LoginSecurity::firstOrNew(array('user_id' => $user->id));
        $login_security->user_id = $user->id;
        $login_security->google2fa_enable = 0;
        $login_security->last_used = Carbon::now()->subDay();
        $login_security->google2fa_secret = Google2FA::generateSecretKey();
        $login_security->save();

        return response()->json([
            'error' => false,
            'message' => 'Chave gerada com sucesso',
            'login' => $login_security,
        ], 200);
    }

    /**
     * Enable 2FA
     */
    public function enable2fa(Request $request){

        $user = Auth::user();
        $secret = $request->input('secret');
        $valid = Google2FA::verifyGoogle2FA($user->loginSecurity->google2fa_secret, $secret, 8);

        if($valid){
            $user->loginSecurity->google2fa_enable = 1;
            $user->loginSecurity->save();

            return response()->json([
                'error' => false,
                'message' => 'Autenticação em 2 fatores ativada com sucesso',
            ], 200);

        }else{
            return response()->json([
                'error' => true,
                'message' => 'Código inválido! Tente novamente',
            ], 404);
        }
    }

    /**
     * Disable 2FA
     */
    public function disable2fa(Request $request){
        if (!(Hash::check($request->get('current-password'), $request->user()->password))) {
            // The passwords matches
            return redirect()->back()->with("error","Your password does not matches with your account password. Please try again.");
        }

        $validatedData = $request->validate([
            'current-password' => 'required',
        ]);
        $user = $request->user();
        $user->loginSecurity->google2fa_enable = 0;
        $user->loginSecurity->save();

        return redirect('/2fa')->with('success',"2FA is now disabled.");
    }

    public function verify2fa(Request $request){
        $authenticator = app(Google2FAAuthenticator::class)->bootStateless($request);

        return $authenticator->makeRequestOneTimePasswordResponse();
    }
}
