<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ImageProduct;
use App\Models\Category;

class SearchController extends Controller
{
    /**
     * Devuelve todas las categorías existentes con sus respectivos ids
     */
    public function getCategories() {
        $categories = Category::all();
        return response()->json(['code' => 200, 'message' => $categories]);
    }

    /**
     * Devuelve las últimas 30 imágenes de la BD para la portada
     */
    public function getLastImages() {
        $files = ImageProduct::take(30)->get();
        $response = [];
        foreach($files as $file) {
            $response[] = $file;
        }
        return response()->json(['code' => 200, 'message' => $files]);
    }

}
