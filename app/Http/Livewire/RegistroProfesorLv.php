<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Registros;
use DateTimeZone;
use DateInterval;
use DateTime;

class RegistroProfesorLv extends Component
{
    use WithPagination;
    use WithFileUploads;

    protected $paginationTheme = 'bootstrap';
    public $nombreInforme = 'Modelado y Simulacion Profe Polonia';  
    public $identificacion;
    public $correo;
    public $nombre;
    public $tipo = 'profesor'; // Valor predeterminado
    public $profesion;
    public $tarjetaProfesionalProfe;
    public $codigo;
    public $programa;
    public $search;

    public $matriz = [];
    public $combinacion = [];
    public $nombreCombinacion = ''; // Nueva variable para el nombre de la combinación
    public $letraEnseñada;
    public $letrasAprendidas = [];

    public function enseñarPerceptron()
    {
        // Inicializar la matriz con 4 columnas y 5 filas
        $this->matriz = array_fill(0, 5, array_fill(0, 4, 0));
        $this->combinacion = [];

        // Ejemplo: La letra "O"
        // Marcamos las celdas necesarias
        $this->matriz[1][0] = 1;
        $this->matriz[1][1] = 1;
        $this->matriz[1][2] = 1;
        $this->matriz[1][3] = 1;

        // Configuramos la combinación para la letra "O"
        $this->combinacion = [1, 1, 1, 1];
    }

    public function guardarMatriz()
    {
        // Guardar la combinación, la matriz aprendida y el nombre de la combinación
        $this->letrasAprendidas[] = [
            'nombre' => $this->nombreCombinacion,
            'matriz' => $this->matriz,
            'combinacion' => $this->combinacion,
        ];

        // Limpiar la matriz, la combinación y el nombre después de guardar
        $this->matriz = array_fill(0, 5, array_fill(0, 4, 0));
        $this->combinacion = [];
        $this->nombreCombinacion = '';
    }

    public function probarPerceptron()
    {
        // Obtener la combinación ingresada por el usuario
        $combinacionUsuario = $this->combinacion;

        // Buscar la letra aprendida más cercana usando el perceptrón
        $letraEnseñada = $this->encontrarLetraEnseñada($combinacionUsuario);

        // Mostrar la letra o un mensaje si no se ha enseñado
        $this->letraEnseñada = $letraEnseñada;
    }

    private function encontrarLetraEnseñada($combinacionUsuario)
    {
        // Lógica para buscar la letra más cercana usando el perceptrón
        // Puedes implementar tu algoritmo aquí
    }

    
    public function render()
    {
        
     /*   return view('livewire.registro-profesor-lv',[
            // 'entregas' =>  $entregas,
             'registros' =>  $registros,
             'registrosAlumnos' =>  $registrosAlumnos,

         ]);*/

       return view('livewire.registro-profesor-lv');
    }
}
