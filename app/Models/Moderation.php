<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Moderation extends Model
{
    use HasFactory;

    protected $table = 'moderations';

    protected $fillable = [
        'product_id',
        'moderator_id',
        'moderator_rol',
        'decision'
    ];
}
