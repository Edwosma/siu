<?php

namespace App\Http\Livewire;
use Illuminate\Support\Facades\Http;

use Livewire\Component;

use Livewire\WithPagination;
use Livewire\WithFileUploads;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\productos;
use App\Models\cotizaciones;
use App\Models\emprendimiento;
use App\Models\Comentario;
use App\Models\Cliente;

use Illuminate\Support\Facades\Mail;
use App\Mail\RegistroEmprendedorMail;
use DateTimeZone;
use DateInterval;
use DateTime;


class HomeLv extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $mostrarFormulario = false;
    public $tipoUsuario = '';
    public $nombreEmprendedor = '';
    public $correoEmprendedor = ''; // Agrega las propiedades para los demás campos del formulario aquí
    public $passwordEmprendedor = '';
    public $identificacionEmprendedor = '';
    public $fechaNacimientoEmprendedor = '';
    public $tipoIdentificacionEmprendedor = '';
    public $search;
    public $searchTerm = '';

    public $bandRegional = 0;
    public $bandNewUsusuario = 0;
    
    public $regionalSeleccionada  =0;
    public $checkOtroCliente = false; 

    public $productName;
    public $precioProducto;
    public $cantidadProducto;
   
     public $cotizaciones = [];
     public $nuevaCotizacion = [];    
     public $informacionRecibida;
     public $bandCarrito = false; 
     public $bandCarritoTabla = false;
     public $bandCarritoUpdate = false;
     public $bandCarritoVacio = false;
     public $tipoEmprendimiento = 0;
     public $tipoCAtegoria;
     public $bandTotalProductos = true; 
     public $bandVerProducto = false;
     public $unidadesCotizar = 1;
     public $comentario =null;
     public $comentarioOk =null;
     public $badLogeoClienteHome = true;
     public $fechaFin;
     
    
    public function mount()
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();   
        $this->idioma = session('configuracion.idioma', 'es');   
        $out->writeln( $this->idioma);
        app()->setLocale($this->idioma);        
        
        $this->retrievedData = session('myDataCliente');
        $out->writeln("Token: " . $this->retrievedData);

        $this->correoCliente = session('correoCliente');
        $out->writeln("correo: " . $this->correoCliente);
        try
        {
            $ch = curl_init();
            $token = $this->retrievedData; // Reemplaza 'tu_token_aqui' con el token real

            curl_setopt_array($ch, array(
                CURLOPT_URL => "http://45.32.164.200/empredimientosUcatolica/public/api/user", // Reemplaza con tu URL de API
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPGET => true,
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer " . $token,
                    "Content-Type: application/json"
                )
            ));

            $result = curl_exec($ch);
            $data = json_decode($result, true);

            
            curl_close($ch); 
        }          
        catch (\Exception $e) 
        {
            $out->writeln("Error al realizar la solicitud a la API: " . $e->getMessage());
            //return redirect('/registroEmprendedor');
            return redirect()->route('registroEmprendedor');
           
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
            $this->badLogeoClienteHome = false;
        }
    }    

    public function increment()
    {
        $this->unidadesCotizar++;
    }

    public function decrement()
    {
        if ($this->unidadesCotizar > 0) {
            $this->unidadesCotizar--;
        }
    }

    public function actualizarCarrito()
    {
        
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln("actualizarCarrito");       

       $productos = cotizaciones::where('estado', 1)  
       ->where('idCliente', $this->idCliente)     
       ->orderby('fechacreacion','desc')
       ->get();       

       $factura = cotizaciones::where('estado', 1)  
       ->where('idCliente', $this->idCliente)     
       ->orderby('fechacreacion','desc')
       ->first();    

       if ($productos && $factura)
       {
        $this->bandCarrito=true;
        $this->bandCarritoTabla=true;             
        $this->cotizaciones = $productos;
        $this->numeroFactura = $factura->numerofactura;
        $this->numeroCotizacion =$factura->id;
       }
       else
       {
            $this->bandCarritoVacio=true;
       }
   }

   public function cerrarSesionHome()
    {
        session()->forget('myDataCliente');
        session()->forget('correoCliente');
        return redirect('/');       
        
    }

   public function carritoEstadoCancelar()
    {        
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln("cambiarEstadoCancelar");
        $this->bandCarrito=false;  
        $this->bandCarritoTabla=false; 
        $this->bandCarritoVacio=false; 
        $this->bandCarritoUpdate=false; 
        $this->cantidadProducto = '';   
        $this->unidadesCotizar =1;   
   }

   public function cambiarEstadoCancelar()
    {        
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln("cambiarEstadoCancelar");
        $this->bandCarrito=false;  
        $this->bandCarritoTabla=false; 
        $this->bandCarritoUpdate=false; 
        $this->cantidadProducto = '';
        $this->bandVerProducto=false;  
        $this->bandTotalProductos=true;  
        $this->unidadesCotizar =1;   
   }

   public function finalVerproducto()
    {        
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln("finalVerproducto");
        $this->bandCarrito=false;  
        $this->bandCarritoTabla=false; 
        $this->bandCarritoUpdate=false; 
        $this->cantidadProducto = '';
   }
   public function eliminarCotizacion()
    {        
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln("eliminarCotizacion");
        $this->bandCarrito=false;   
        $this->bandCarritoTabla=false;       
        $this->carritoEstadoCancelar(); 
        session()->flash('messageSuccess','Se eliminó correctamente la cotizacion...');
        
            
   }
   public function cancelarUpdate()
    {        
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln("cancelarUpdate");
        $this->bandCarritoUpdate=false; 
        $this->cantidadProducto = '';        
        $this->bandCarritoTabla=true;         
   }

   public function comentarProducto($idEmprendimiento)
    {        
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln("comentarProducto");
        

        if (empty($this->comentario)) {
            session()->flash('message', 'Por favor, escriba un comentario....');
            return;
        }  

        $this->comentarioOk = $this->comentario;

        $newComentario = Comentario::create([
            'comentario' => $this->comentarioOk, 
            'cliente_id' =>  $this->idCliente, 
            'emprendedimiento_id' => $idEmprendimiento,
            'estado' => 0, 
            'visualizacion_id' => 1,
        ]);

        session()->flash('messageSuccess','Se envió el comentario correctamente...'); 
        $this->comentarioOk = '';        
        $this->comentario = '';   
        $this->cambiarEstadoCancelar();     
              
   }
   public function newCotizacion($idProductoCotizar)
   {   
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();  
        $validacionCarrito = cotizaciones::where('idCliente', $this->idCliente)
        ->where('estado', 1)
        ->first();

        try
        {
            if ($validacionCarrito->estado==1)
            {
                $out->writeln( "YA tenia cotizacion");      
                $productCotizar = productos::where('id', $idProductoCotizar)->first();          
                cotizaciones::Create([
                    'numerofactura' => $validacionCarrito->numerofactura,
                    'referencia' => $productCotizar->referencia, 
                    'nombre' => $productCotizar->nombre,
                    'precio' => $productCotizar->precio, 
                    'unidades' => $this->unidadesCotizar,
                    'idemprendimiento' =>$this->idEmprendimientoCotizar,
                    'estado' => 1,
                    'idCliente' => $this->idCliente,
                ]);
               
            }
            else
            {
                $hoy = new  DateTime();
                $hoy->setTimezone(new DateTimeZone('America/Bogota'));
                $fechaActual = $hoy->format('Ymd hh:mm:ss');

                $fechaHoy = $hoy->format('d/m/Y');
                $horaHoy = $hoy->format('H:i:s');

                $out->writeln( "YA tenia cotizacion estado no 1");    
                $productCotizar = productos::where('id', $idProductoCotizar)->first();
                $newCotizaciones = cotizaciones::create([
                    'numerofactura' => 'COT_' . $horaHoy,
                    'referencia' => $productCotizar->referencia, 
                    'nombre' => $productCotizar->nombre,
                    'precio' => $productCotizar->precio, 
                    'unidades' => $this->unidadesCotizar,
                    'idemprendimiento' =>$this->idEmprendimientoCotizar,
                    'estado' => 1,
                    'idCliente' => $this->idCliente,
                ]);
                 
            }
         }
        catch (\Exception $e) 
        {
            $hoy = new  DateTime();
                $hoy->setTimezone(new DateTimeZone('America/Bogota'));
                $fechaActual = $hoy->format('Ymd hh:mm:ss');

                $fechaHoy = $hoy->format('d/m/Y');
                $horaHoy = $hoy->format('H:i:s');
                

            $out->writeln( "catch Nueva cotizacion");    
            $productCotizar = productos::where('id', $idProductoCotizar)->first();
            $newCotizaciones = cotizaciones::create([
                'numerofactura' => 'COT_' .  $horaHoy,
                'referencia' => $productCotizar->referencia, 
                'nombre' => $productCotizar->nombre,
                'precio' => $productCotizar->precio, 
                'unidades' => $this->unidadesCotizar,
                'idemprendimiento' =>$this->idEmprendimientoCotizar,
                'estado' => 1,
                'idCliente' => $this->idCliente,
            ]);
            $this->unidadesCotizar =1;  
        }
   }

   public function enviarCotizacion()
    {        
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln("enviarCotizacion");
        $out->writeln( $this->numeroCotizacion);

        $dataEmprendimiento = cotizaciones::where('id', $this->numeroCotizacion)->first();
        $enviarCotizacion = cotizaciones::where('numerofactura', $dataEmprendimiento->numerofactura)      
        ->get();    

        $emprendimientoCotizacion = emprendimiento::where('id', $dataEmprendimiento->idemprendimiento)->first();
        $emprendedorCotizacion = User::where('id', $emprendimientoCotizacion->emprendedor_id)->first();        
        foreach ($enviarCotizacion as $cot) 
        {
            $cot->update([  
                'estado' => 2,  
            ]);  
        }
            

        $out->writeln( $emprendedorCotizacion->email);  
        $this->bandCarrito=false; 
        $this->bandCarritoTabla=false;         

        $mailData = [
            'title' => 'Nueva solicitud de cotizacion',
            'body' => 'El cliente '.$this->nombreCliente. ' ha solicitado una cotizacion en la plataforma, con el siguiente correo: '.$this->correoCliente
        ];

        Mail::to($emprendedorCotizacion->email)->send(new RegistroEmprendedorMail($mailData));
        $this->unidadesCotizar =1;   
        session()->flash('messageSuccess','Se envió correctamente la cotizacion...');
   }
   public function actualizarProducto($idProductoCotizado)
    {        
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln("actualizarProducto");
        $this->bandCarritoTabla=false; 

        $producto = cotizaciones::where('id', $idProductoCotizado)      
       ->first();  
       $this->productoUpdate =  $producto;
       $this->cantidadProducto =  $producto->unidades;
       $this->bandCarritoUpdate=true; 
       
   }
   public function guardarUpdateproducto()
    {        
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln("guardarUpdateproducto");
        $out->writeln("cantidad");
        $out->writeln($this->cantidadProducto);
        $productoActualizar = cotizaciones::where('id', $this->productoUpdate->id)      
        ->first();  

        $productoActualizar->update([  
            'unidades' => $this->cantidadProducto,  
        ]);
        $this->cambiarEstadoCancelar();
        session()->flash('messageSuccess','Cotizacion actualizada correctamente...');  
        
   }  
   public function verProducto($idProducto)
    {        
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln("verProducto");
        $out->writeln($idProducto);
        
        $productoSeleccionado = productos::where('id', $idProducto)->first();
        $emprendimientoAsociado = emprendimiento::where('id',$productoSeleccionado->emprendimiento->id)->first();
        $this->idEmprendimientoCotizar =$productoSeleccionado->emprendimiento->id;
        $this->comentarios = Comentario::where('emprendedimiento_id',$productoSeleccionado->emprendimiento->id)
        ->orderBy('created_at', 'desc')
        ->get();

        $out->writeln($productoSeleccionado->nombre);
        $out->writeln($emprendimientoAsociado->nombre_emprendimiento);
        $this->productoSeleccionado = $productoSeleccionado;
        $this->emprendimientoAsociado = $emprendimientoAsociado;
       $this->bandTotalProductos=false;
       $this->bandVerProducto=true;      
   }

    public function render()
    {       
        $filtrosCategorias  =  array();

     
       if($this->tipoEmprendimiento>0)
        {
            $fCategoria = [
                'tipoEmprendimiento', $this->tipoEmprendimiento
            ];
            array_push($filtrosCategorias, $fCategoria);
        } 
        
        $productos = productos::where('estado', 2)
        ->where($filtrosCategorias)
        ->where(function ($search) {
            $search->where('nombre', 'like', '%' . $this->search . '%')
                    ->orwhere('referencia', 'like', '%' . $this->search . '%');                    
        })
        ->orderby('fecha_creacion','desc')
        ->get();         

        return view('livewire.homeDos-lv',[
            'productos' =>  $productos, 
        ]);   
       
    }
    
    
}
