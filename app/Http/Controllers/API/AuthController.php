<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //Insert a newuser in DB unless its email is already taken
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
}
