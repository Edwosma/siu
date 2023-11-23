<?php

namespace App\Http\Livewire;
use Illuminate\Support\Str;

use Livewire\Component;
use Livewire\WithPagination;

use Illuminate\Support\Facades\DB;
use App\Models\AdministradorErrores;
use Illuminate\Support\Facades\Http;


use Illuminate\Support\Facades\Storage;

use App\Models\User;
use App\Models\emprendimiento;
use App\Models\productos;
use App\Models\Cliente;

use Illuminate\Support\Facades\Mail;
use App\Mail\RegistroEmprendedorMail;

class EmprendedoresHomeLv extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $bandActualizar;
    public $bandGestionar;
    public $search;
    public $estadoGestionSeleccionado= 2;
    public $estadoGestionEmprendedor= 'TODOS';
    public $estadoGestionEmprendimiento= 'TODOS';    
    public $bandGestionEmprendedores= false;
    public $bandGestionEmprendimientos= false;
    public $bandGestionProductos= false;
    public $bandGestionarProducto;
    public $respuestaGestionProducto= 0;

    public function mount()
    {
        // Obtener valores almacenados previamente, si existen
        $this->idioma = session('configuracion.idioma', 'es');
        //$this->retrievedData = session('configuracion.idioma');
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln( $this->idioma);
        app()->setLocale($this->idioma);

        $this->calculoDatos();
        



    }
    public function calculoDatos()
    {
        $this->totalProductosPendientes = productos::where('estado',1)->pluck('id'); 
        $this->productosPendientes = count($this->totalProductosPendientes);

        $this->totalEmprendimientos = emprendimiento::where('estado',1)->pluck('id'); 
        $this->pendientesEmprendimientos = count($this->totalEmprendimientos);

        $this->totalPendientesEmprendedores = User::where('estado',1)->pluck('id'); 
        $this->pendientesEmprendedores = count($this->totalPendientesEmprendedores);
    }

    public function updatingSearch()
    {       
        $this->resetPage();
    }

    public function detalleProducto($idProducto)
    {
        $this->productoDetalle = productos::find($idProducto) ;
        $this->bandGestionarProducto = 1;
        $this->comentarioGestionproducto= $this->productoDetalle->comentarioAdmin;
        $this->respuestaGestionProducto =0;
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln("foto");
        $out->writeln($this->productoDetalle->fotos);

    }

    public function cerrarSesionAdmin()
    {
        session()->forget('adminOk');      
        return redirect('/registroEmprendedor');       
        
    }

    public function enviarInformeGerencial()
    {
        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => "http://45.32.164.200/empredimientosUcatolica/public/api/informe-gerencial",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPGET => true,
        ));

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($httpCode == 200) {
            $data = json_decode($result, true);

            // Manejar los datos según sea necesario
            if ($data) {
                $informacion = $data; // Puedes acceder a la información como un array asociativo
                // Realiza cualquier acción necesaria con la información obtenida
            } else {
                // Manejar el caso en que no se pudo decodificar el JSON
            }
        } else {
            // Manejar el caso en que la solicitud no fue exitosa
            echo "Error: HTTP $httpCode";
        }
    }

    public function aprobarProducto()
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln("aprobarProducto");

        $out->writeln("Gestion");
        $out->writeln($this->respuestaGestionProducto);     
        $out->writeln("comentario");   
        if($this->comentarioGestionproducto)
        {
            $out->writeln($this->comentarioGestionproducto);
        }
        else
        {
            $this->comentarioGestionproducto= 'Gestionado';
            $out->writeln($this->comentarioGestionproducto);          
        }     

        $productoGestionarNew = productos::find($this->productoDetalle->id);
        $productoCorreo = $productoGestionarNew->nombre;
        $out->writeln($productoCorreo);
        $productoGestionarNew->update([         
            'respuestaAdmin' => $this->comentarioGestionproducto,  
            'estado' => $this->respuestaGestionProducto,               
        ]);        

        

        $emprendimientoCotizacion = emprendimiento::where('id', $productoGestionarNew->idEmprendimiento)->first();
        $emprendedorCotizacion = User::where('id', $emprendimientoCotizacion->emprendedor_id)->first();        

        if ($this->respuestaGestionProducto == 2)
        {
            $respuestaCorreo = 'Aprobada';
        }
        else
        {
            if ($this->respuestaGestionProducto == 3)
            {
                $respuestaCorreo = 'Rechazada';
            }
        }
        $mailData = [
            'title' => 'Respuesta solicitud gestion de producto',
            'body' => 'Tu producto '.$productoCorreo. '  fue '.$respuestaCorreo . ' con el siguiente comentario: '.$this->comentarioGestionproducto
        ];

        Mail::to($emprendedorCotizacion->email)->send(new RegistroEmprendedorMail($mailData));

        $this->respuestaGestionProducto =0;
        $this->comentarioGestionproducto = '' ;
        $this->productoDetalle = '';
        $productoGestionarNew = '';

        $this->homeAdminitracionDeUsuarios();

           
        session()->flash('messageSuccess', 'Solicitud de creacion de producto, gestionada con exito...');
        $this->calculoDatos();
    }

    public function detalleUsuario($idUsuario)
    {
        
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln("detalleUsuario");
        $this->usuarioDetalle = User::find($idUsuario) ;
        

        switch ($this->usuarioDetalle->estado_registro) 
        {
            case 1:
                $this->bandGestionar = 1;
                break;
            case 2:
                $this->bandActualizar = 1;
                break;
            case 3:
                $this->bandGestionar = 3;
                break;
            default:               
                break;
        }
        
        
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln($this->usuarioDetalle->name);
        $out->writeln($this->usuarioDetalle->id);
        if($this->usuarioDetalle->estado == 1)
        {            
            $this->checkOtroCliente = true;   
        }
        else
        {           
            $this->checkOtroCliente = false; 
        }
       
    }

    public function actualizacionRegionalUsuario()
    {          

        User::where('id', $this->usuarioDetalle->id)
        ->update([            
            'estado' => ($this->checkOtroCliente == 1)? 1:0,   
            'estado_registro' => 1,         

        ]);
        
        $this->bandActualizar = 0;

        session()->flash('messageSuccess', 'Informacion actualizada.');
        return;

        $this->resetPage();
        $this->calculoDatos();

    }

    public function homeAdminitracionDeUsuarios()
    {
        $this->bandActualizar = 0;
        $this->regionalSeleccionada = "";
        $this->bandGestionar = 0;
        $this->bandGestionarProducto = 0;
        
    }


    public function updatedCheckOtroCliente()
    {
        $this->resetPage();
    }
    
    public function aprobarEmprendedor()
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $ch = curl_init();
        $data = array(
            'identificacionEmprendedor' => $this->usuarioDetalle->identification,
            'correoEmprendedor' => $this->usuarioDetalle->email,
            'estadoSolicitud' => 1,
            
        );
        
        $payload = json_encode($data);
        
        // Configura los encabezados, incluyendo el token
        $headers = array(
            "Content-Type: application/json",           
        );
        
        curl_setopt_array($ch, array(
            CURLOPT_URL => "http://45.32.164.200/empredimientosUcatolica/public/api/gestionar-registro-emprendedor",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => $headers, // Establece los encabezados con el token
        ));
        
        $result = curl_exec($ch);
        $array = json_decode($result, true);
        $respuestaWebService = $array["datos"];
        $respuestaWebServiceCode = $array["codigo"];

        if ($respuestaWebServiceCode == 100)
        {
            if (isset($respuestaWebService['mensajePersonalizado'])) {
                $mensaje = $respuestaWebService['mensajePersonalizado'];
                session()->flash('messageSuccess', $mensaje);
            }
        }
        else
        {
            if (isset($respuestaWebService['mensajePersonalizado'])) {
                $mensaje = $respuestaWebService['mensajePersonalizado'];
                session()->flash('message', $mensaje);
            }
        }       
        
            $out->writeln($respuestaWebService);
            $out->writeln("NOTIFICACION FIN");

        $this->identificacionEmprendedor = '';
        $this->correoEmprendedor = '';
        $this->estadoSolicitud = ''; 

        $this->bandActualizar = 0;
        $this->bandGestionar = 0;      
        session()->flash('messageSuccess', 'Emprendedor, solicitud aprobada...');
        $this->calculoDatos();
    }

    public function rechazarEmprendedor()
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $ch = curl_init();
        $data = array(
            'identificacionEmprendedor' => $this->usuarioDetalle->identification,
            'correoEmprendedor' => $this->usuarioDetalle->email,
            'estadoSolicitud' => 2,
            
        );
        
        $payload = json_encode($data);
        
        // Configura los encabezados, incluyendo el token
        $headers = array(
            "Content-Type: application/json",           
        );
        
        curl_setopt_array($ch, array(
            CURLOPT_URL => "http://45.32.164.200/empredimientosUcatolica/public/api/gestionar-registro-emprendedor",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => $headers, // Establece los encabezados con el token
        ));
        
        $result = curl_exec($ch);
        $array = json_decode($result, true);
        $respuestaWebService = $array["datos"];
        $respuestaWebServiceCode = $array["codigo"];

        if ($respuestaWebServiceCode == 100)
        {
            if (isset($respuestaWebService['mensajePersonalizado'])) {
                $mensaje = $respuestaWebService['mensajePersonalizado'];
                session()->flash('messageSuccess', $mensaje);
            }
        }
        else
        {
            if (isset($respuestaWebService['mensajePersonalizado'])) {
                $mensaje = $respuestaWebService['mensajePersonalizado'];
                session()->flash('message', $mensaje);
            }
        }       
        
           $out->writeln($respuestaWebService);
            $out->writeln("NOTIFICACION FIN");

        $this->identificacionEmprendedor = '';
        $this->correoEmprendedor = '';
        $this->estadoSolicitud = ''; 

        $this->bandActualizar = 0;
        $this->bandGestionar = 0;      
        session()->flash('message', 'Emprendedor, solicitud rechazada...');
        $this->calculoDatos();
    }

    public function cambiarEstadoCancelar()
    {        
        $this->bandActualizar = 0;
        $this->bandGestionar = 0; 
        $this->resetPage();
    }


    
    
    public function render()
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln("Tipo filtro");
        
        switch ($this->estadoGestionSeleccionado) {
            case 0:               
                $this->bandGestionProductos= true;
                $this->bandGestionEmprendimientos= false;
                $this->bandGestionEmprendedores= false;
                break;
            case 1:             
                $this->bandGestionProductos= false;
                $this->bandGestionEmprendimientos= true;
                $this->bandGestionEmprendedores= false;
                break;
            case 2:               
                $this->bandGestionProductos= false;
                $this->bandGestionEmprendimientos= false;
                $this->bandGestionEmprendedores= true;
                break;
            default:                
                break;
        }

        if($this->bandGestionEmprendedores)
        {
            if($this->estadoGestionEmprendedor)
            {
                $filtrosGestion  =  array();
                if($this->estadoGestionEmprendedor != 'TODOS'){
                    $out = new \Symfony\Component\Console\Output\ConsoleOutput();
                    $out->writeln($this->estadoGestionEmprendedor);
                    $fEstado = [
                        'estado_registro', $this->estadoGestionEmprendedor
                    ];
                    array_push($filtrosGestion, $fEstado);
                }

                $emprendedores = User::where(function ($query) use ($filtrosGestion) {
                        $query->where($filtrosGestion)
                            ->where(function ($search) {
                                $search->where('name', 'like', '%' . $this->search . '%')
                                    ->orWhere('identification', 'like', '%' . $this->search . '%');
                            });
                    })
                    ->orderby('created_at','desc')
                    ->paginate(5);
                    $emprendimientos = [];
                    $productos = [];
            }
            else
            {
                $emprendedores= [];
            }
        }
      

        if($this->bandGestionEmprendimientos)
        {
            if($this->estadoGestionEmprendimiento)
            {
                $filtrosGestionEmpre  =  array();
                if($this->estadoGestionEmprendimiento != 'TODOS')
                {
                    $out = new \Symfony\Component\Console\Output\ConsoleOutput();
                 
                    $out->writeln($this->estadoGestionEmprendimiento);
                    $fEstadoEmpre = [
                        'estado', $this->estadoGestionEmprendimiento,
                       


                    ];
                    array_push($filtrosGestionEmpre, $fEstadoEmpre);
                }     
                $emprendimientos = emprendimiento::where(function ($query) use ($filtrosGestionEmpre) {
                    $query->where($filtrosGestionEmpre)
                        ->where(function ($search) {
                            $search->where('nombre_emprendimiento', 'like', '%' . $this->search . '%');
                            
                        });
                })
                ->orderby('created_at','desc')
                ->paginate(5);  
                $emprendedores= [];    
                $productos= []; 
            }
            else
            {
                $emprendimientos = [];
            }
        }
     
      

        if($this->bandGestionProductos)
        {
            $productos = productos::where('estado', 1)
            ->where(function ($search) {
                $search->where('nombre', 'like', '%' . $this->search . '%')
                       ->orwhere('referencia', 'like', '%' . $this->search . '%');
            })
            ->orderby('fecha_creacion','desc')
            ->paginate(5);
            $emprendedores = [];
            $emprendimientos = [];
                      
        }
        else
        {
            $productos = [];
        }
     
    
       
        
        return view('livewire.emprendedores-home-lv',[
             'productos' =>  $productos,
             'emprendimientos' =>  $emprendimientos,
             'emprendedores' =>  $emprendedores,
 
         ]);

       
    }
}
