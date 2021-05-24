<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Image;
use App\Models\Category;

class SearchController extends Controller
{
    /**
     * Devuelve las últimas 50 imágenes de la BD
     */
    public function getLastImages() {
        $filename = Image::latest()->first();
        $filename = $filename->filename;
        return Storage::disk('s3')->response('images/' . $filename);
        //return Storage::disk('s3')->get('images/' . $filename);
    }

    /**
     * Devuelve todas las categorías existentes con sus respectivos ids
     */
    public function getCategories() {
        $categories = Category::all();
        return response()->json(['code' => 200, 'message' => $categories]);
    }
}
