<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Image;


class ImageController extends Controller
{
    public function create() {
        return view('test');
    }

    public function store(Request $request) {
        $path = $request->file('image')->store('images', 's3');
        return $path;
    }

    public function show(Image $image) {

    }
}
