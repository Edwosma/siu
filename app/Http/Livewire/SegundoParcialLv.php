<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use App\Models\User;
use App\Models\OpcionesPreguntasParcial;
use App\Models\PreguntasParcial;

class SegundoParcialLv extends Component
{
    use WithFileUploads;

    public $identificacionUsuario;
    public $mensaje;
    public $bandExamen = false;
    public $jsonFile;
    public $jsonFilePreguntas;
    public $preguntas = [];
    public $answers = [];
    public $isFileLoaded = false;
    public $isFileLoadedPreguntas = false;
    public $dataProcessed = false;
    public $dataProcessedPreguntas = false;
    protected $listeners = [
        'fileLoaded' => 'processJsonData',
        'fileLoadedPreguntas' => 'processJsonDataPreguntas',
    ];
    public $tiempoParcial;
    public $banModificarParcial = false;
    public $banPresentarExamen = false;
    public $tiempoEsperaInput = 5;
    public $preguntas2;
    public $respuestas;
    public $examenFinalizado = false;
    public $calificacion = 0;

    public function mount()
    {
        $this->preguntas2 = PreguntasParcial::with('opciones')->get()->toArray();
        $this->respuestas = array_fill(0, count($this->preguntas2), null);
    }

    public function enviarRespuestas()
    {
        $calificacion = 0;

        foreach ($this->preguntas2 as $index => $pregunta) {
            $respuestaCorrecta = $pregunta['opciones']
                ->where('esRespuestaCorrecta', true)
                ->pluck('opcion')
                ->first();
            $respuestaUsuario = $this->respuestas[$index];

            if ($respuestaUsuario === $respuestaCorrecta) {
                $calificacion++;
            }
        }

        $this->calificacion = $calificacion;
        $this->examenFinalizado = true;
    }

    public function finalizarConfiguracion()
    {
        $cont = 1;

        if ($this->dataProcessed && $this->dataProcessedPreguntas) {
            $preguntasIDs = [];

            foreach ($this->preguntas[0]['questions'] as $question) {
                $respuesta = PreguntasParcial::create([
                    'pregunta' => $question['question'],
                    'estado' => $cont,
                ]);
                $cont++;
                $preguntasIDs[$question['question']] = $respuesta->id;
            }

            foreach ($this->preguntas[0]['questions'] as $question) {
                $idPregunta = $preguntasIDs[$question['question']];
                foreach ($question['options'] as $option) {
                    OpcionesPreguntasParcial::create([
                        'idPregunta' => $idPregunta,
                        'opcion' => $option,
                    ]);
                }
            }

            $cont2 = 1;

            foreach ($this->answers[0]['answersKey'] as $pregunta => $respuestaCorrecta) {
                PreguntasParcial::where('estado', $cont2)
                    ->update([
                        'respuestacorrecta' => $respuestaCorrecta,
                    ]);
                $cont2++;
            }

            $this->answers = [];
            $this->preguntas = [];
            $this->dataProcessed = false;
            $this->dataProcessedPreguntas = false;
            $cont2 = 1;
            $cont = 1;

            $this->emit('dataSaved');
        }

        $this->banPresentarExamen = false;
        $this->banModificarParcial = false;
    }

    public function processJsonData()
    {
        if ($this->jsonFile) {
            $jsonData = json_decode(file_get_contents($this->jsonFile->getRealPath()), true);

            if ($jsonData) {
                $this->answers = $jsonData;
                $this->dataProcessed = true;
            }

            $this->jsonFile = null;
        }
    }

    public function processJsonDataPreguntas()
    {
        if ($this->jsonFilePreguntas) {
            $jsonDataPreguntas = json_decode(file_get_contents($this->jsonFilePreguntas->getRealPath()), true);

            if ($jsonDataPreguntas) {
                $this->preguntas = $jsonDataPreguntas;
                $this->dataProcessedPreguntas = true;
            }

            $this->jsonFilePreguntas = null;
        }
    }

    public function updatedJsonFilePreguntas()
    {
        $this->validate([
            'jsonFilePreguntas' => 'required|mimetypes:application/json',
        ]);
    }

    public function updatedJsonFile()
    {
        $this->validate([
            'jsonFile' => 'required|mimetypes:application.json',
        ]);
    }

    public function presentarExamen()
    {
        $this->banPresentarExamen = true;
        $this->banModificarParcial = false;
    }

    public function ajustarParcial()
    {
        $this->banModificarParcial = true;
        $this->banPresentarExamen = false;
    }

    public function cancelarConfiguracion()
    {
        $this->banPresentarExamen = false;
        $this->banModificarParcial = false;
    }

    public function salirPresentarParcial()
    {
        $this->bandExamen = false;
        $this->banPresentarExamen = false;
        $this->banModificarParcial = false;
        $this->mensaje = '';
        $this->identificacionUsuario = '';
    }

    public function validacionUsuario()
    {
        $validacionUser = User::where('identification', $this->identificacionUsuario)->first();

        if ($validacionUser) {
            $this->mensaje = "Si está registrado, a continuación encontrará el examen, TIENE " . $this->tiempoEsperaInput . "min para resolverlo ¡Buena suerte, " . $validacionUser->name . "! att:Rodrigo Aranda";
            $this->bandExamen = true;
        } else {
            $this->mensaje = "Actualmente no está registrado...Se relajáron. Lamentablemente, no puede continuar. Regístrese o comuníquese con el administrador.";
        }
    }

    public function render()
    {
        return view('livewire.segundo-parcial-lv');
    }
}

