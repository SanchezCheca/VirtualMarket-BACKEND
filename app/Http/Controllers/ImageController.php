<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\ImageProduct;
use App\Models\Tag;
use Auth;
use Intervention\Image\ImageManagerStatic;
use Illuminate\Support\Facades\File;


class ImageController extends Controller
{
    public function create()
    {
        return view('test');
    }

    //FUNCIÓN PARA PRUEBAS DESDE WEB
    public function store(Request $request)
    {
        //Guarda el archivo original
        $path = $request->file('image')->store('images', 's3');
        $filename = basename($path);
        //Crea y guarda la imagen de muestra (con marca de agua)
        $sampleImage = ImageManagerStatic::make($request->file('image'))->insert(('watermark.png'))->save($filename);
        Storage::disk('s3')->put('samples/' . $filename, $sampleImage);
        //Crea y guarda la miniatura (reducida a un ancho de 612px)
        $thumbnail = ImageManagerStatic::make($request->file('image'))->resize(612, null, function ($constraint) {
            $constraint->aspectRatio();
        })->save($filename);
        Storage::disk('s3')->put('thumbnails/' . $filename, $thumbnail);

        //Elimina el archivo temporal creado
        File::delete($filename);

        //Recoge los datos para la BD
        $format = $request->file('image')->extension();
        $width = ImageManagerStatic::make($request->file('image'))->width();
        $height = ImageManagerStatic::make($request->file('image'))->height();

        //Crea el registro en BD
        $image = ImageProduct::create([
            'creator_id' => 1,
            'price' => 1.23,
            'filename' => $filename,
            'format' => $format,
            'width' => $width,
            'height' => $height
        ]);

        return Storage::disk('s3')->response('thumbnails/' . $filename);
    }

    public function show(ImageProduct $image)
    {
        return Storage::disk('s3')->response('images/' . $image->filename);
    }

    /**
     * Recoge una imagen, la guarda en bd y la sube al servidor S3
     */
    public function upload(Request $request)
    {
        //Guarda el archivo original
        $path = $request->file('image')->store('images', 's3');
        $filename = basename($path);
        //Crea y guarda la imagen de muestra (con marca de agua)
        $sampleImage = ImageManagerStatic::make($request->file('image'))->insert(('watermark.png'))->save($filename);
        Storage::disk('s3')->put('samples/' . $filename, $sampleImage);
        //Crea y guarda la miniatura (reducida a un ancho de 612px)
        $thumbnail = ImageManagerStatic::make($request->file('image'))->resize(612, null, function ($constraint) {
            $constraint->aspectRatio();
        })->save($filename);
        Storage::disk('s3')->put('thumbnails/' . $filename, $thumbnail);

        //Elimina el archivo temporal creado
        File::delete($filename);

        //Recoge los datos para la BD
        $format = $request->file('image')->extension();
        $width = ImageManagerStatic::make($request->file('image'))->width();
        $height = ImageManagerStatic::make($request->file('image'))->height();
        $price = $request->input('price');
        $category = $request->input('category');

        //Crea el registro en BD
        $image = ImageProduct::create([
            'creator_id' => auth('api')->user()->id,
            'category_id' => $category,
            'price' => $price,
            'filename' => $filename,
            'format' => $format,
            'width' => $width,
            'height' => $height
        ]);

        //Guarda las etiquetas
        $tags = $request->input('tags');
        $tagsSinEspacios = str_replace(' ','',$tags);
        $tagsArray = explode(',', $tagsSinEspacios);
        foreach ($tagsArray as $tag) {
            $tagActual = Tag::where('name','LIKE',$tag)->first();
            if ($tagActual == null) {
                //La etiqueta introducida no existe en BD, se crea
                $tagActual = Tag::create([
                    'name' => $tag
                ]);
            }
            //Se asigna la etiqueta introducida a la publicación
            DB::table('imageProduct_tag')->insert([
                'image_id' => $image->id,
                'tag_id' => $tagActual->id
            ]);
        }

        return response()->json(['message' => ['exito' => true, 'message' => 'La imagen se ha guardado correctamente'], 'code' => 200], 200);
    }
}
