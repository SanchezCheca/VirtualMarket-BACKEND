<?php

namespace App\Http\Controllers;

use App\Models\ImageProduct;
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
}
