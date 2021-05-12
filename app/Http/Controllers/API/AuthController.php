<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Inserta un nuevo usuario en la BD a menos que el email o el nombre de usuario estén registrados
     * ---
     * Insert a new user in DB unless its email is already taken
     */
    public function register(Request $req)
    {
        if (User::where('username', 'LIKE', $req->input('username'))->count() > 0) {
            //El nombre de usuario ya está registrado
            return response()->json(['message' => ['success' => false, 'message' => 'Nombre de usuario en uso'], 'code' => 200], 200);
        } else if (User::where('email', 'LIKE', $req->input('email'))->count() > 0) {
            //El correo ya está registrado
            return response()->json(['message' => ['success' => false, 'message' => 'Correo en uso'], 'code' => 200], 200);
        }

        $validatedData = $req->validate([
            'username' => 'required',
            'name' => 'required',
            'email' => 'email|required|unique:users',
            'password' => 'required'
        ]);

        $validatedData['password'] = \Hash::make($req->input('password'));

        $user = User::create($validatedData);
        $accessToken = $user->createToken('authToken')->accessToken;

        return response()->json(['message' => ['success' => true, 'user' => $user, 'access_token' => $accessToken], 'code' => 201], 201);
    }

    /**
     * Comproueba que coincidan usuario/email y contraseña y devuelve el access_token y los datos del usuario
     * ---
     * Checks user or email and password match and returns access_token and user data
     */
    public function login(Request $request) {
        /*
        $loginData = $request->validate([
            'emailorusername' => 'required',
            'password' => 'required'
        ]);*/

        //Comprueba si existe un nombre de usuario con el dato introducido
        if (User::where('username','LIKE',$request->input('emailorusername'))->count() > 0) {
            //El dato 'emailorusername' corresponde a un nombre de usuario
            $email = User::select('email')->where('username', 'LIKE', $request->input('emailorusername'))->get();
        } else {
            $email = $request->input('emailorusername');
        }

        $loginData = [
            'email' => $email,
            'password' => $request->input('password')
        ];
        
        if (!auth()->attempt($loginData,true)) {
            return response()->json(['message' => 'La combinación correo/nombre de usuario y contraseña no es correcta. CORREO: ' . $email, 'code' => 400], 400);
        }

        $user = User::where('email', '=', $request->input('email'))
                ->get();
                
        $accessToken = auth()->user()->createToken('authToken')->accessToken;

        return response()->json(['message' => ['user' => auth()->user(), 'access_token' => $accessToken, 'datos_user' => $user], 'code' => 200], 200);
    }
}
