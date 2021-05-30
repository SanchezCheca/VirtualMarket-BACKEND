<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ImageProduct;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Auth;

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

    /**
     * Actualiza la información de un usuario cuyo 'username' recibe por parámetro
     */
    public function updateUser($username, Request $request) {
        //Comprueba que el usuario que ha iniciado sesión es el que va a ser modificado
        if (auth('api')->user()->username == $request->input('username')) {
            $username = $request->input('username');
            $name = $request->input('name');
            $email = $request->input('email');

            $user = User::where('username','LIKE',$username)->first();
            $user->name = $name;
            $user->email = $email;
            $user->save();

            return response()->json(['message' => ['message' => 'Correcto'], 'code' => 200], 200);
        } else {
            //El usuario iniciado no es el que se está intentando actualizar
            return response()->json(['message' => ['message' => 'No has iniciado sesión'], 'code' => 401], 401);
        }
    }

    /**
     * Devuelve la información útil para mostrar en perfil de un usuario dado
     */
    public function getUserData(Request $request) {
        $user = User::where('username','LIKE',$request->username)->first();
        if ($user != null) {
            //EL USUARIO EXISTE, SE RECOGE LA INFORMACIÓN ÚTIL
            $nImages = ImageProduct::where('creator_id','=',$user->id)->count(); //nº de imagenes subidas por un usuario
            $userImages = ImageProduct::where('creator_id','=',$user->id)->get();   //'filenames' de las imágenes del usuario

            //Comprueba si está siguiendo al usuario
            $isFollowing = false;
            if (auth('api')->user() && $user->id != auth('api')->user()->id) {
                //No se trata del usuario que ha iniciado sesión
                $isFollowing = \DB::select('SELECT * FROM user_following WHERE user_id = ? AND user_following_id = ?', [auth('api')->user()->id,$user->id]);
                if ($isFollowing != null) {
                    $isFollowing = true;
                } else {
                    $isFollowing = false;
                }
            }

            //Nº de seguidores
            $nFollowers = \DB::table('user_following')->where('user_following_id','=',$user->id)->count();

            //Nº de gente a la que sigue
            $nFollowing = \DB::table('user_following')->where('user_id','=',$user->id)->count();

            //Prepara el paquete que se mandará al front
            $userData = [
                'name' => $user->name,
                'username' => $user->username,
                'nImages' => $nImages,
                'userImages' => $userImages,
                'isFollowing' => $isFollowing,
                'nFollowers' => $nFollowers,
                'nFollowing' => $nFollowing
            ];

            return response()->json(['message' => ['userData' => $userData], 'code' => 200], 200);
        } else {
            return response()->json(['message' => ['message' => 'El usuario no existe'], 'code' => 404], 404);
        }
    }

    /**
     * El usuario iniciado sigue al usuario cuyo 'username' recibe
     */
    public function followUser(Request $request) {
        $usuarioIniciado = auth('api')->user();
        if ($usuarioIniciado) {
            //Ha iniciado sesión, comprueba que no esté siguiendo ya al usuario
            $userId = User::where('username','LIKE',$request->username)->first()->id;
            $isFollowing = \DB::select('SELECT * FROM user_following WHERE user_id = ? AND user_following_id = ?', [auth('api')->user()->id,$userId]);
            if (!$isFollowing) {
                //No está siguiendo, le sigue
                \DB::insert('INSERT INTO user_following VALUES (?,?)', [$usuarioIniciado->id, $userId]);
                return response()->json(['message' => ['message' => 'Correcto'], 'code' => 201], 201);
            } else {
                //Ya le está siguiendo
                return response()->json(['message' => ['message' => 'Ya sigues al usuario'], 'code' => 409], 409);
            }
        } else {
            //No ha iniciado sesión
            return response()->json(['message' => ['message' => 'No has iniciado sesión'], 'code' => 401], 401);
        }
    }

    /**
     * El usuario iniciado deja de seguir al usuario cuyo 'username' recibe
     */
    public function unfollowUser(Request $request) {
        $usuarioIniciado = auth('api')->user();
        if ($usuarioIniciado) {
            //Ha iniciado sesión, comprueba que no esté siguiendo ya al usuario
            $userId = User::where('username','LIKE',$request->username)->first()->id;
            $isFollowing = \DB::select('SELECT * FROM user_following WHERE user_id = ? AND user_following_id = ?', [auth('api')->user()->id,$userId]);
            if (!$isFollowing) {
                //No está siguiendo al usuario
                return response()->json(['message' => ['message' => 'No sigues al usuario'], 'code' => 409], 409);
            } else {
                //Le está siguiendo, le deja de seguir
                \DB::delete('DELETE FROM user_following WHERE user_id = ? AND user_following_id = ?', [$usuarioIniciado->id,$userId]);
                return response()->json(['message' => ['message' => 'Correcto'], 'code' => 201], 201);
            }
        } else {
            //No ha iniciado sesión
            return response()->json(['message' => ['message' => 'No has iniciado sesión'], 'code' => 401], 401);
        }
    }
}
