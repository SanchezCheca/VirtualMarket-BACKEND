<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImageProduct extends Model
{
    use HasFactory;

    protected $table = 'imageProducts';

    //protected $guarded = [];

    protected $fillable = [
        'creator_id',
        'category_id',
        'price',
        'filename',
        'format',
        'width',
        'height'
    ];
}
