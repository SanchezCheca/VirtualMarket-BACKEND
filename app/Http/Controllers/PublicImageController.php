<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\ImageProduct;
use App\Models\User;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;

/**
 * RECUPERA LAS IMÁGENES DE LA BASE DE DATOS S3 PARA QUE SEAN UTILIZADAS POR CLIENTES
 */
class PublicImageController extends Controller
{
    //Devuelve la miniatura (imagen reducida) de la imagen cuyo id recibe por parámetro
    public function getThumbnail($filename) {
        $image = ImageProduct::where('filename','LIKE',$filename)->first();
        if ($image) {
            return Storage::disk('s3')->response('thumbnails/' . $image->filename);
        } else {
            //Si la imagen solicitada no existe devuelve a otro sitio
            return redirect('https://github.com/sanchezcheca');
        }
    }

    //Devuelve la muestra (imagen completa con marca de agua) de la imagen cuyo id recibe por parámetro
    public function getSample($filename) {
        $image = ImageProduct::where('filename','LIKE',$filename)->first();
        if ($image) {
            return Storage::disk('s3')->response('samples/' . $image->filename);
        } else {
            //Si la imagen solicitada no existe devuelve a otro sitio
            return redirect('https://github.com/sanchezcheca');
        }
    }

    //Devuelve una imagen de perfil
    public function getProfileImage($filename) {
        $image = User::where('profileImage','LIKE',$filename)->first();
        if ($image) {
            return Storage::disk('s3')->response('profileImages/' . $filename);
        } else {
            //Si la imagen solicitada no existe devuelve a otro sitio
            return redirect('https://github.com/sanchezcheca');
        }
    }

    //Descarga la imagen original si está autorizada
    public function download($filename) {
        $image = ImageProduct::where('filename','LIKE',$filename)->first();
        if ($image && $image->type == 0) {
            $image->type = 1;
            $image->save();



            //Storage::disk('local')->put($image->filename, Storage::disk('s3')->response('images/' . $image->filename));
            //return response()->download($image->filename)->deleteFileAfterSend(true);
            return Storage::disk('s3')->download('images/' . $image->filename);
            //return Response::download(Storage::disk('s3')->response('samples/' . $image->filename));
        } else {
            return redirect('https://release.d2uhek8z3i2n0r.amplifyapp.com/');
        }
    }
}
