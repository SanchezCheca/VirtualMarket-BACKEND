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
        $files = ImageProduct::where('status','=',2)->take(300)->orderByDesc('created_at')->get();
        $response = [];
        foreach($files as $file) {
            $response[] = $file;
        }
        return response()->json(['code' => 200, 'message' => $files]);
    }

    /**
     * Devuelve los resultados de una búsqueda
     */
    public function search($search) {
        $resultado = null;

        //Recupera las imágenes que contienen como etiqueta el texto $search
        $resultado = \DB::select('SELECT * FROM imageProducts WHERE id IN (SELECT image_id FROM imageProduct_tag WHERE tag_id IN (SELECT id FROM tags WHERE name LIKE ?)) AND status = 2', [$search]);

        return response()->json(['code' => 200, 'message' => $resultado]);
    }

}
