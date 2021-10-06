<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\User;
use App\Models\Tag;
use App\Models\Moderation;
use App\Models\ImageProduct;

class ModController extends Controller
{
    /**
     * Devuelve los datos necesarios de una imagen en estado "En revisión"
     */
    public function getImageToModerate(Request $request) {
        $respuesta = $this->loadImageToModerate();

        return response()->json(['code' => 200, 'message' => $respuesta]);
    }

    /**
     * Aprueba una imagen
     */
    public function voteImage(Request $request) {
        $imageId = $request->imageId;
        $decision = $request->decision;
        $user = auth('api')->user();
        $imageObj = ImageProduct::find($imageId);

        //--Comprobaciones de seguridad (es usuario moderador o administrador, la imagen no es suya, no la ha votado ya y no ha sido aprobada/rechazada)
        if (($user->rol == 1 || $user->rol == 2) && $imageObj->creator_id != $user->id && Moderation::where('product_id','=',$imageId)->where('moderator_id','=',$user->id)->count() == 0 && $imageObj->status == 1) {
            //Las comprobaciones son correctas :)
            //Se guarda el voto
            $moderation = new Moderation;
            $moderation->product_id = $imageObj->id;
            $moderation->moderator_id = $user->id;
            $moderation->moderator_rol = $user->rol;
            $moderation->decision = $decision;
            $moderation->save();

            //Manda a comprobar el status del producto moderado
            $this->checkStatus($imageObj->id);

            //Se forma la respuesta, hay que volver a cargar la imagen para moderar
            $respuesta = $this->loadImageToModerate();
            
            return response()->json(['code' => 200, 'message' => $respuesta]);
        } else {
            //No es posible aceptar la solicitud. Se devuelve otra imagen para moderar
            $respuesta = $this->loadImageToModerate();
            return response()->json(['code' => 403, 'message' => $respuesta]);
        }
    }

    //------------------------------------ MÉTODOS PRIVADOS
    /**
     * Devuelve la imagen, el nº de imágenes que tiene que moderar un usuario y las estadísticas de moderador de dicho usuario
     */
    private function loadImageToModerate() {
        //La imagen es la más antigüa de entre las que tienen el 'status' a 0 y NO ha sido revisada por el mismo usuario
        $respuesta = new \stdClass();
        $userId = auth('api')->user()->id;

        //Pasa el objeto imageProduct completo
        $imagen = \DB::select('SELECT * from imageProducts WHERE imageProducts.creator_id != ? AND imageProducts.status = 1 AND imageProducts.id NOT IN (SELECT moderations.product_id FROM moderations WHERE moderations.moderator_id = ?) ORDER BY imageProducts.created_at ASC LIMIT 1;', [$userId,$userId]);
        if ($imagen) {
            $imagen = $imagen[0];

            //Añade el NOMBRE del usuario creador a la respuesta
            $imagen->creatorUsername = User::find($imagen->creator_id)->username;

            //Añade la lista de etiquetas a la respuesta
            $bdTagList = \DB::select('SELECT name FROM tags WHERE id IN (SELECT tag_id FROM imageProduct_tag WHERE image_id = ?)', [$imagen->id]);
            $tagList = [];
            foreach ($bdTagList as $tag) {
                $tagList[] = $tag->name;
            }
            $imagen->tagList = $tagList;
        } else {
            $imagen = null;
        }

        //Añade las estadísticas del usuario con respecto al sistema de moderación
        $userStats = [];
        $votedImages = Moderation::where('moderator_id','=',$userId)->count();  //nº de imágenes que ha votado

        if ($votedImages != 0) {
            $approvedAndVotedByUser = \DB::select('SELECT COUNT(*) AS approved FROM moderations WHERE moderations.id IN (SELECT id FROM imageProducts WHERE imageProducts.status = 2) AND moderations.moderator_id = ?', [$userId]);
            $approvedImagesRate = $approvedAndVotedByUser[0]->approved / $votedImages;  //Tasa imágenes aceptadas por el usuario / imágenes aprobadas finalmente

            $rejectedAndVotedByUser = \DB::select('SELECT COUNT(*) AS rejected FROM moderations WHERE moderations.id IN (SELECT id FROM imageProducts WHERE imageProducts.status = 3) AND moderations.moderator_id = ?', [$userId]);
            $rejectedImagesRate = $rejectedAndVotedByUser[0]->rejected / $votedImages;  //Tasa imágenes rechazadas por el usuario / imágenes rechazadas finalmente
        } else {
            $approvedImagesRate = 0;
            $rejectedImagesRate = 0;
        }

        $userStats += [
            'votedImages' => $votedImages,
            'approvedImagesRate' => $approvedImagesRate,
            'rejectedImagesRate' => $rejectedImagesRate
        ];

        //Añade el nº total de imágenes que le quedan a un usuario por moderar
        $nImagesBD = \DB::select('SELECT COUNT(*) AS nImages from imageProducts WHERE imageProducts.creator_id != ? AND imageProducts.status = 1 AND imageProducts.id NOT IN (SELECT moderations.product_id FROM moderations WHERE moderations.moderator_id = ?)', [$userId,$userId]);
        $nImages = $nImagesBD[0]->nImages;

        //Forma la respuesta
        $respuesta->image = $imagen;
        $respuesta->userStats = $userStats;
        $respuesta->nImages = $nImages;

        return $respuesta;
    }

    /**
     * Cambia el status de una imagen si procede (lel producto ha sido aprobado/rechazado por 3 usuarios moderadores o por 1 usuario administrador)
     */
    private function checkStatus($imageId) {
        $image = ImageProduct::find($imageId);
        if ($image && $image->status == 1) {
            $adminVotes = Moderation::where('moderator_rol','=',1)->count();
            if ($adminVotes >= 1) {
                //Tiene votos de administradores, se tiene en cuenta el primero (que haya más de 1 se considera un error)
                if (Moderation::where('product_id','=',$image->id)->first()->decision == 1) {
                    //El producto ha sido APROBADO por un administrador, se acepta
                    $image->status = 2;
                    $image->save();
                } else {
                    //El producto ha sido RECHAZADO por un administrador, se rechaza
                    $image->status = 3;
                    $image->save();
                }
            } else {
                //No tiene votos de administradores, se cuentan los votos de los moderadores
                if (Moderation::where('product_id','=',$image->id)->where('moderator_rol','=',2)->where('decision','=',1)->count() >= 3) {
                    //Ha sido APROBADO por 3 o más moderadores, se acepta
                    $image->status = 2;
                    $image->save();
                } else if (Moderation::where('product_id','=',$image->id)->where('moderator_rol','=',2)->where('decision','=',0)->count() >= 3) {
                    //Ha sido RECHAZADO por 3 o más moderadores
                    $image->status = 3;
                    $image->save();
                }
            }
        }
    }
}
