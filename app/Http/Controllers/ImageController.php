<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Request;
use App\Models\Image;
use Auth;


class ImageController extends Controller
{
    public function create() {
        return view('test');
    }

    public function store(Request $request) {
        $path = $request->file('image')->store('images', 's3');

        $image = Image::create([
            'creator_id' => 1,
            'price' => 1.23,
            'filename' => basename($path),
            'url' => Storage::disk('s3')->url($path)
        ]);

        return Storage::disk('s3')->response('images/' . $image->filename);
    }

    public function show(Image $image) {
        return Storage::disk('s3')->response('images/' . $image->filename);
    }

    /**
     * Recoge una imagen, la guarda en bd y la sube al servidor S3
     */
    public function upload(Request $request) {
        $path = $request->file('image')->store('images', 's3');

        $price = $request->input('price');
        
        $idUsuario = $request->user()->id;
        
        $image = Image::create([
            'creator_id' => $idUsuario,
            'price' => $price,
            'filename' => basename($path),
            'url' => Storage::disk('s3')->url($path)
        ]);

        return response()->json(['message' => ['message' => $idUsuario], 'code' => 200], 200);
    }
}
