<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Config;

class ConfiguracionPlataformaLv extends Component
{   
    public $idioma = 'es';
    public $color = '#18151E'; // Color blanco como valor predeterminado

    public function mount()
    {
        // Obtener valores almacenados previamente, si existen
        //$this->idioma = session('configuracion.idioma', 'en');
        $this->color = session('myColor', '#18151E');
        $this->idioma = session('configuracion.idioma', 'es');
        app()->setLocale($this->idioma);
    }

    public function cambiarIdioma()
   
    {

        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln("cambiarIdioma");
        $out->writeln($this->idioma);

        $locale = $this->idioma ?? 'es'; // Si $this->idioma está vacío, establece 'es' como predeterminado
       
        // Establecer el locale en la configuración
        //Config::set('app.locale', $locale);
        // app()->setLocale($locale);

        // También puedes almacenar el idioma actual en la sesión si es necesario
        session()->put('configuracion.idioma', $locale);

        // Emite el evento para actualizar la interfaz de usuario
        $this->emit('refresh');
    }

public function cambiarColor()
{
    $out = new \Symfony\Component\Console\Output\ConsoleOutput();
    $out->writeln("cambiarColor");
    $out->writeln( $this->color);
    //session(['configuracion.color' => $this->color]);

    session()->put('myColor',  $this->color);

    $this->emit('refresh', $this->color);
}

    public function reiniciarValoresPredeterminados()
    {
        // Restablecer los valores predeterminados
        $this->idioma = 'es';
        $this->color = '#18151E';

        // Limpiar los valores almacenados
        session()->forget('configuracion.idioma');
        session()->forget('myColor');

        $this->emit('refresh', $this->color);
    }


    public function render()
    {
        return view('livewire.configuracion-plataforma-lv');
    }
}
