<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
class GestionEmprendedoresLv extends Component
{
    use WithPagination;
   public $emprendedores = [];
   public $bandEmprendedores= false;
   public $search = '';
   public $page = 1;

    public function getEmprendedores()
    {
        // Realiza la solicitud GET a la API
        $response = \Http::get('http://45.32.164.200/berakha/public/api/emprendedores');

        // Obtiene los datos de la respuesta JSON
        $emprendedores = $response->json();

        // Asigna los emprendedores a una propiedad del componente
        $this->emprendedores = $emprendedores;
        $this->bandEmprendedores= true;
    }

    public function render()
    {
        return view('livewire.gestion-emprendedores-lv');
    }
}
