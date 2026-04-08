<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Nivel;
use App\Models\Institucion;

class Tema extends Model
{
    protected $fillable = [
        'nombre',
    ];
}
