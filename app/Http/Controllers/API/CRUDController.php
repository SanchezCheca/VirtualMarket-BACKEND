<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CRUDController extends Controller
{
    public function getAllUsersData() {
        $usuarios = User::take(300)->orderByDesc('created_at')->get();
        return response()->json(['code' => 200, 'message' => $usuarios]);
    }

    public function updateUser(Request $request) {
        $user = User::where('id','=',$request->input('id'))->first();

        if ($request->input('name') != null) {
            $user->name = $request->input('name');
        }
        if ($request->input('email') != null) {
            $user->email = $request->input('email');
        }
        if ($request->input('rol') != null) {
            $user->rol = $request->input('rol');
        }
        if ($request->input('balance') != null) {
            $user->balance = $request->input('balance');
        }

        $user->save();

        return response()->json(['code' => 200, 'message' => 'OK']);
    }

    public function removeUser(Request $request) {
        User::where('id','=',$request->input('id'))->delete();
        return response()->json(['code' => 200, 'message' => 'OK']);
    }
}
