<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $fillable = [
        'creator_id',
        'category_id',
        'price',
        'filename',
        'url'
    ];
}
