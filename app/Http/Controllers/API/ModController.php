<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ModController extends Controller
{
    /**
     * Devuelve los datos necesarios de una imagen en estado "En revisión"
     */
    public function getImageToModerate(Request $request) {
        //La imagen es la más antigüa de entre las que tienen el 'status' a 0 y NO ha sido revisada por el mismo usuario
        
        //$resultado = \DB::select('SELECT * FROM imageProducts WHERE id IN (SELECT image_id FROM imageProduct_tag WHERE tag_id IN (SELECT id FROM tags WHERE name LIKE ?)) AND status = 2', [$search]);
        //SELECT * from imageProducts WHERE imageProducts.status = 1 AND imageProducts.id NOT IN (SELECT moderations.product_id FROM moderations WHERE moderations.moderator_id = USERID) ORDER BY imageProducts.created_at ASC;
    }
}
