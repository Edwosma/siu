<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreguntasParcial extends Model
{
    use HasFactory;
    protected $table = 'tablapreguntas';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'pregunta',
        'respuestacorrecta',
        'respuestausuario',
        'estado'
    ];

    public function opciones()
    {
        return $this->hasMany(OpcionesPreguntasParcial::class, 'idpregunta', 'estado');
    }
}
