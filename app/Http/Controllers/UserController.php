<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\LdapService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function login(Request $request)
    {
        try{

            $validator = Validator::make($request->all(), [
                'email' => 'required',
                'password' => 'required',
            ]);

            if($validator->fails()){
                return response()->json([
                    'error' => true,
                    'message' => $validator->errors()
                ], 401);
            }

            $result = LdapService::connect($request->get('email'), $request->get('password'));


            if(!$result){
                return response()->json([
                    'error' => true,
                    'message' => 'Email ou senha inválidos.',
                ], 401);
            }

            $user = User::firstOrCreate([
                'email' => $request->get('email'),
            ], [
                'name' => $result['user'],
            ]);

            return response()->json([
                'error' => false,
                'message' => 'Usuário autenticado com sucesso',
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'id' => $user->id,
                ],
                'token' => $user->createToken($user->name, ['server:create', 'server:update'])->plainTextToken
            ], 200);

        }catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function register(Request $request)
    {
        try {

            $attr = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|unique:users,email',
                'password' => 'required|string|min:6'
            ]);

            $user = User::create([
                'name' => $attr['name'],
                'password' => bcrypt($attr['password']),
                'email' => $attr['email']
            ]);

            return response()->json([
                'erro' => false,
                'token' => $user->createToken($user->email, ['user:create', 'user:update'])->plainTextToken
            ], 200);

        }catch(Exception $e){
            return response()->json([
            'error' => true,
               'message' => $e->getMessage()
            ], 500);
        }

    }
}
