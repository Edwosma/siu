<?php

namespace App\Http\Livewire;
use Illuminate\Support\Str;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

use Illuminate\Support\Facades\DB;
use App\Models\AdministradorErrores;
use Illuminate\Support\Facades\Http;
use App\Models\Cliente;
use App\Models\cotizaciones;
use App\Models\User;
use App\Models\emprendimiento;

use Illuminate\Support\Facades\Storage;
class ClientesAdminLv extends Component
{
    public $search;
    public $nombre;
    public $tipoDocumento;
    public $documento;
    public $fechaNacimiento;
    public $correo;
    public $contraseña;
    public $confirmarContraseña;
    public $aceptarTerminos;
    public $aceptarTratamientoDatos;
    public $mensaje;
    public $bandRegistro = false;
    public $bandIniciarSesion = true;
    public $email;
    public $password;  
    public $badLogeoCliente = true;
    public $estadoGestionSeleccionado= 4;
    public$bandPendienteCotizacion = false;
    public$bandGestionadaCotizacion = false;
    protected $listeners = ['cargarGraficaInit'];
    public $cotizacionPendiente;
    public $cotizacionRealizada ;

    public function mount()
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();       
        $this->retrievedDataCliente = session('myDataCliente');
        $out->writeln("Token: " . $this->retrievedDataCliente);

        $this->correoCliente = session('correoCliente');
        $out->writeln("correo: " . $this->correoCliente);
        
        $this->idioma = session('configuracion.idioma', 'es'); 
          
        $out->writeln( $this->idioma);
        app()->setLocale($this->idioma);

        try
        {
            $ch = curl_init();
            $token = $this->retrievedDataCliente; // Reemplaza 'tu_token_aqui' con el token real

            curl_setopt_array($ch, array(
                CURLOPT_URL => "http://45.32.164.200/empredimientosUcatolica/public/api/cliente", // Ajusta la URL para obtener información del cliente
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPGET => true,
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer " . $token,
                    "Content-Type: application/json"
                )
            ));

            $result = curl_exec($ch);
            $data = json_decode($result, true);

            if ($data) 
            {
                $userId = $data['id'];                    
                $out->writeln("ID del usuario: " . $userId);                
                $this->idEmprendedor= $userId;
            } 
            curl_close($ch); 
        }          
        catch (\Exception $e) 
        {
            $out->writeln("Error al realizar la solicitud a la API: " . $e->getMessage());
            //return redirect('/registroEmprendedor');
            return redirect('/registroCliente');
           
            $out->writeln("Sin Token: ");
        }

        if ($this->correoCliente)
        {
            $clienteLogeado = Cliente::where('correo', $this->correoCliente)->first();
            $this->idCliente = $clienteLogeado->id;
            $this->nombreCliente = $clienteLogeado->nombre;
            $out->writeln($this->idCliente);
            $out->writeln($this->nombreCliente);
        }
        else
        {
            $this->badLogeoCliente = false;
        }
      
    }

  public function emitirEventos()
  {
    $cotizacionPendiente = cotizaciones::where('estado', 2)->count();
    $cotizacionRealizada = cotizaciones::where('estado', 3)->count();
    
    $datosCumplimiento = [
        'cotizacionPendiente' => $cotizacionPendiente,
        'cotizacionRealizada' => $cotizacionRealizada       
    ];

    $this->emit('ActualizarGraficaCumplimiento', $datosCumplimiento);

    
  }

  public function cargarGraficaInit($titulo)
    {
        $this->emitirEventos();
    }

    public function seleccionarCotizacion($numerofactura)
    {        
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();   
        $out->writeln("seleccionarCotizacion ");
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();   
        $out->writeln($numerofactura);
        $this->cotizacionSelec = cotizaciones::where('numerofactura', $numerofactura)->first();
        $this->cotizacionVer = cotizaciones::where('numerofactura', $numerofactura)->get();
        $out->writeln($this->cotizacionSelec->estado);
        $validacion = $this->cotizacionSelec->estado;        
        switch ($validacion) 
        {
            case 3:               
                $this->bandPendienteCotizacion =false;
                $this->bandGestionadaCotizacion =true;                  

                break;
            case 2:         
                $this->bandGestionadaCotizacion =false;    
                $this->bandPendienteCotizacion =true;                
                break;
            case 1:               
                $this->bandPendienteCotizacion =false;
                $this->bandGestionadaCotizacion =false;                  

                break;
            
            default:                
                break;
        }          
    }

    public function cotizacionEstadoCancelar()
    {
        $this->bandPendienteCotizacion =false;
        $this->bandGestionadaCotizacion =false;         
    }

    public function exportarPDF()
    {
              
        
    }

    public function cerrarSesion()
    {
        session()->forget('myDataCliente');
        session()->forget('correoCliente');       
        return redirect('/registroCliente');       
        
    }

    public function redireccionarLogeoCliente()
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln("redireccionarLogeo:");
        return redirect()->to('/registroCliente');      
    }

    public function render()
    {
        if ($this->correoCliente && $this->idCliente)
        {        
            switch ($this->estadoGestionSeleccionado) 
            {
                case 4:               
                    $cotizacionesCliente = cotizaciones::where('idCliente', $this->idCliente)   
                    ->where('estado', '>', 1)      
                    ->where(function ($search) {
                        $search->where('nombre', 'like', '%' . $this->search . '%')
                                ->orwhere('referencia', 'like', '%' . $this->search . '%');                    
                    })
                    ->orderby('fechacreacion','desc')
                    ->get();  
                    break;
                case 2:             
                    $cotizacionesCliente = cotizaciones::where('idCliente', $this->idCliente)
                    ->where('estado', 2)
                    ->where(function ($search) {
                        $search->where('nombre', 'like', '%' . $this->search . '%')
                                ->orwhere('referencia', 'like', '%' . $this->search . '%');                    
                    })
                    ->orderby('fechacreacion','desc')
                    ->get();  
                    break;
                case 3:               
                    $cotizacionesCliente = cotizaciones::where('idCliente', $this->idCliente)
                    ->where('estado', 3)
                    ->where(function ($search) {
                        $search->where('nombre', 'like', '%' . $this->search . '%')
                                ->orwhere('referencia', 'like', '%' . $this->search . '%');                    
                    })
                    ->orderby('fechacreacion','desc')
                    ->get();  
                    break;
                default:                
                    break;
            }          

            

            return view('livewire.clientes-admin-lv',[
                'cotizacionesCliente' =>  $cotizacionesCliente, 
            ]);   
        }
        else
        {
            return view('livewire.clientes-admin-lv');
        }

        
    }
}
