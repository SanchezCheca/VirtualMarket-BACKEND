<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //Insert a new user in DB unless its email is already taken
    public function register(Request $req)
    {

        if (User::where('email', 'LIKE', $req->input('email'))->count() > 0) {
            //User email is already taken
            return response()->json(['message' => ['success' => false, 'message' => 'Correo en uso'], 'code' => 400], 400);
        }

        $validatedData = $req->validate([
            'name' => 'required',
            'email' => 'email|required|unique:users',
            'password' => 'required'
        ]);

        $validatedData['password'] = \Hash::make($req->input('password'));

        $user = User::create($validatedData);
        $accessToken = $user->createToken('authToken')->accessToken;

        return response()->json(['message' => ['success' => true, 'user' => $user, 'access_token' => $accessToken], 'code' => 201], 201);
    }

    //Checks user or email and password match and returns access_token and user data
    public function login(Request $request) {
        $loginData = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);
        
        if (!auth()->attempt($loginData,true)) {
            return response()->json(['message' => 'Login incorrecto. Revise las credenciales.', 'code' => 400], 400);
        }

        $user = User::where('email', '=', $request->input('email'))
                ->get();
                
        $accessToken = auth()->user()->createToken('authToken')->accessToken;

        return response()->json(['message' => ['user' => auth()->user(), 'access_token' => $accessToken, 'datos_user' => $user], 'code' => 200], 200);
    }
}
