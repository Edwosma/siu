<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpcionesPreguntasParcial extends Model
{
    use HasFactory;
    protected $table = 'tablaopciones';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'idpregunta',
        'opcion'
    ];
}
