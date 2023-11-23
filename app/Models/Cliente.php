<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Cliente extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $hidden = [
        'clave',
        'remember_token',
    ];

    public function visualizaciones()
    {
        return $this->hasMany('App\Models\Visualizacion'::class, 'cliente_id');
    }

    public function comentarios()
    {
        return $this->hasMany('App\Models\Cliente'::class, 'cliente_id');
    }
}
