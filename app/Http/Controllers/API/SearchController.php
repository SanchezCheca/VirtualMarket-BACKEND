<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Image;

class SearchController extends Controller
{
    /**
     * Devuelve las últimas 50 imágenes de la BD
     */
    public function getLasts() {
        //$images = [];
        $filename = Image::latest()->first();
        $filename = $filename->filename;
        return Storage::disk('s3')->response('images/' . $filename);
        //return Storage::disk('s3')->get('images/' . $filename);
    }
}
