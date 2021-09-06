<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\ImageProduct;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Http\Request;
use Auth;

class PurchasesController extends Controller
{
    /**
     * Compra de un producto
     * Se recibe: buyerUsername, imageFilename
     */
    public function buyProduct(Request $request) {
        $loggedUser = User::where('id','LIKE',auth('api')->user()->id)->first();
        $imageFilename = $request->input('imageFilename');

        if ($request->input('buyerUsername') == $loggedUser->username) {
            //El comprador es el usuario que ha iniciado sesión
            $image = ImageProduct::where('filename','LIKE',$imageFilename)->first();

            //Comprueba si hay saldo suficiente
            if ($loggedUser->balance >= $image->price) {
                //Se efectúa la compra
                $purchase = new Purchase;
                $purchase->buyer_id = $loggedUser->id;
                $purchase->seller_id = $image->creator_id;
                $purchase->image_id = $image->id;
                $purchase->price = $image->price;
                $purchase->save();

                //Se resta el saldo
                $loggedUser->balance -= $image->price;
                $loggedUser->save();

                //Se añade el saldo al vendedor
                $seller = User::where('id','=',$image->creator_id)->first();
                $seller->balance += $image->price;
                $seller->save();

                return response()->json(['message' => '¡Has comprado la imagen!', 'code' => 200], 200);
            } else {
                return response()->json(['message' => 'No tienes saldo suficiente', 'code' => 400], 400);
            }
        } else {
            return response()->json(['message' => 'Ha ocurrido algún error', 'code' => 400], 400);
        }
    }

    /**
     * Permite una descarga de una imagen si el usuario iniciado la compró
     */
    public function download(Request $request) {
        $image = ImageProduct::where('filename','LIKE',$request->input('filename'))->first();
        $purchase = Purchase::where('buyer_id','=',auth('api')->user()->id)
        ->where('image_id','=',$image->id)
        ->first();
        if ($purchase) {
            //Se puede descargar la foto
            $image->type = 0;
            $image->save();
            return response()->json(['message' => 'Correcto', 'code' => 200], 200);
        } else {
            return response()->json(['message' => 'No has comprado la imagen', 'code' => 400], 400);
        }
    }
}
