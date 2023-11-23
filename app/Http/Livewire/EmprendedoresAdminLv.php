<?php

namespace App\Http\Livewire;
use Illuminate\Support\Str;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

use Illuminate\Support\Facades\DB;
use App\Models\AdministradorErrores;
use Illuminate\Support\Facades\Http;


use Illuminate\Support\Facades\Storage;


use App\Models\emprendimiento;
use App\Models\productos;
use App\Models\cotizaciones;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegistroEmprendedorMail;
use App\Models\Cliente;

class EmprendedoresAdminLv extends Component
{
    use WithPagination;
    use WithFileUploads;
    public $banCrearEmprendimiento = false;
    public $banCrearProducto = false;
    public $emprendimientosCreados = [];
    protected $listeners = ['emprendimientosFetched' => 'verEmprendimientos'];
    public $nombreEmprendimiento;
    public $descripcionEmprendimiento;
    public $categoriaEmprendimiento;
    public $banInfoEmprendimiento = false;
    public $emprendimientoSeleccionadoOk;
    public $bandemprendimientosCreados =false;
    public $nombre;
    public $referencia;
    public $cantidad;
    public $precio;
    public $descripcion;
    public $imagenProducto;
    public $banInfoProducto = false;
    public $search;
    public $banActualizarEmprendimiento = false;
    public $idEmprendimiento;
    public $banActualizarProducto = false;
    public $nombreUpdate;
    public $referenciaUpdate;
    public $cantidadUpdate;
    public $precioUpdate;
    public $descripcionUpdate;
    public $imagenProductoUpdate;
    public $nombreEmprendimientoUpdate;
    public $descripcionEmprendimientoUpdate;
    public $badLogeo = true;
    public $cotizacionesProductos = null;

    
    public function mount()
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();       
        $this->retrievedData = session('myData');
        $out->writeln("Token: " . $this->retrievedData);
        
        $this->idioma = session('configuracion.idioma', 'es');   
        $out->writeln( $this->idioma);
        app()->setLocale($this->idioma);

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

            if ($data) 
            {
                $userId = $data['id'];    
                $userNombre = $data['name'];       
                $out->writeln("ID del usuario: " . $userId);
                $out->writeln("Nombre del usuario: " . $userNombre);
                $this->nameEmprendedor= $userNombre;
                $this->idEmprendedor= $userId;

            } else 
            {
                $this->badLogeo = false;
            }
            curl_close($ch); 
        }          
        catch (\Exception $e) 
        {
            $out->writeln("Error al realizar la solicitud a la API: " . $e->getMessage());
            //return redirect('/registroEmprendedor');
            return redirect()->route('registroEmprendedor');
           
            $out->writeln("Sin Token: ");
        }
    }

    public function redireccionarLogeo()
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln("redireccionarLogeo:");
        return redirect()->to('/registroEmprendedor');      
    }

    public function verEmprendimientos($data)
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln("verEmprendimientos:");
        $this->emprendimientosCreados = $data;   
        $this->bandemprendimientosCreados = true;     
    }

    
    public function reiniciarDatos(){
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln(" reiniciar datos");
        $this->nit = '';
   
        $this->imagenProducto = null;
       
        $this->reset(['clientId','nameCliente', 'emailCliente', 'emailClientePrevio','periodic', 'period', 'hours','password2','password2_confirmation','estadoClienteFinalCheck','bandAccesoPlataforma']);

    }
    
    public function  crearEmprendimiento()
    {
        $this->banCrearEmprendimiento = true;
        $this->bandemprendimientosCreados = false;
    }
    

    public function registrarEmprendimiento()
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln("registrarEmprendimiento:");
        $out->writeln($this->nombreEmprendimiento);
        $out->writeln($this->descripcionEmprendimiento);
        $out->writeln($this->categoriaEmprendimiento);

        $ch = curl_init();
        $data = array(
            'idEmprendor' => $this->idEmprendedor,
            'tipoEmprendimiento' => $this->categoriaEmprendimiento,
            'nombreEmprendimiento' => $this->nombreEmprendimiento,
            'descripcionEmprendimiento' => $this->descripcionEmprendimiento,
        );
        
        $payload = json_encode($data);
        
        // Configura los encabezados, incluyendo el token
        $headers = array(
            "Content-Type: application/json",
            "Authorization: Bearer " . $this->retrievedData,//token
        );
        
        curl_setopt_array($ch, array(
            CURLOPT_URL => "http://45.32.164.200/empredimientosUcatolica/public/api/crear-emprendimiento",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => $headers, // Establece los encabezados con el token
        ));
        
        $result = curl_exec($ch);
        $array = json_decode($result, true);
        $respuestaWebService = $array["datos"];
        $respuestaWebServiceCode = $array["code"];

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

        $this->categoriaEmprendimiento = '';
        $this->nombreEmprendimiento = '';
        $this->descripcionEmprendimiento = ''; 
        $this->estadoCancelar();      
        return redirect()->route('homeAdminEmpredimientos');
    }

    
    

    public function eliminarEmprendimiento($idEmprendimiento)
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln("eliminarEmprendimiento:");
        $out->writeln($this->idEmprendedor);
        $out->writeln($idEmprendimiento);
        

        $ch = curl_init();
        $data = array(
            'idEmprendor' => $this->idEmprendedor,
            'idEmprendimiento' => $idEmprendimiento,            
        );
        
        $payload = json_encode($data);
        
        // Configura los encabezados, incluyendo el token
        $headers = array(
            "Content-Type: application/json",
            "Authorization: Bearer " . $this->retrievedData,//token
        );
        
        curl_setopt_array($ch, array(
            CURLOPT_URL => "http://45.32.164.200/empredimientosUcatolica/public/api/eliminar-emprendimiento",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => $headers, // Establece los encabezados con el token
        ));
        
        $result = curl_exec($ch);
        $array = json_decode($result, true);
        $respuestaWebService = $array["datos"];

        $respuestaWebServiceCode = $array["code"];
        /*
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
        }  */             
            
         $out->writeln("NOTIFICACION FIN");
         $out->writeln($respuestaWebService);
        //session()->flash('message',json_encode($respuestaWebService));

        $this->categoriaEmprendimiento = '';
        $this->nombreEmprendimiento = '';
        $this->descripcionEmprendimiento = ''; 
        
        
        $this->estadoCancelar();      

        return redirect()->route('homeAdminEmpredimientos');


        
    }

    public function actualizarEmprendimiento()
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln("actualizarEmprendimiento:");

       
        $out->writeln($this->idEmprendimiento);
        $out->writeln($this->idEmprendedor);
        

       

        $ch = curl_init();
        $data = array(
            'idEmprendor' => $this->idEmprendedor,
            'idEmprendimiento' => $this->idEmprendimiento,             
            'nombreEmprendimiento' => $this->nombreEmprendimientoUpdate,
            'descripcionEmprendimiento' => $this->descripcionEmprendimientoUpdate,           
        );
        
        $payload = json_encode($data);
        
        // Configura los encabezados, incluyendo el token
        $headers = array(
            "Content-Type: application/json",
            "Authorization: Bearer " . $this->retrievedData,//token
        );
        
        curl_setopt_array($ch, array(
            CURLOPT_URL => "http://45.32.164.200/empredimientosUcatolica/public/api/actualizar-emprendimiento",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => $headers, // Establece los encabezados con el token
        ));
        
        $result = curl_exec($ch);
        $array = json_decode($result, true);
        $respuestaWebService = $array["datos"];

        $respuestaWebServiceCode = $array["code"];
        
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

        //session()->flash('message',json_encode($respuestaWebService));
       
        $this->nombreEmprendimiento = '';
        $this->descripcionEmprendimiento = ''; 
        $this->estadoCancelar();     
        return redirect()->route('homeAdminEmpredimientos'); 
    }

    public function  estadoCancelar()
    {
        $this->banCrearEmprendimiento = false;
        $this->banCrearProducto = false;
        $this->banInfoEmprendimiento = false;       
        $this->bandemprendimientosCreados = true;     
        $this->banActualizarEmprendimiento = false;
        
    }

    public function  seleccionarEmprendimiento($idEmprendimiento)
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln("seleccionarEmprendimiento:");
        $out->writeln($idEmprendimiento);
        $this->idEmprendimiento = $idEmprendimiento;

        $emprendimientoSeleccionado = emprendimiento::where('id', $idEmprendimiento)->first();
        $this->cotizacionesPendientes = cotizaciones::where('idemprendimiento', $idEmprendimiento)->get();
        $this->emprendimientoSeleccionadoOk =$emprendimientoSeleccionado;
        $out->writeln($this->emprendimientoSeleccionadoOk->descripcion);
        $this->banCrearEmprendimiento = false;
        $this->banCrearProducto = false;
        $this->banInfoEmprendimiento = true;
        $this->bandemprendimientosCreados = false;

    }

    public function  crearProducto($idEmprendimientoCrearPr)
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln("crearProducto:");
        $out->writeln($idEmprendimientoCrearPr);
        $this->idEmprendimientoNewProduct = $idEmprendimientoCrearPr;
        $this->banInfoEmprendimiento = false;
        $this->banCrearProducto = true;
        $this->banInfoEmprendimiento = false;
        $this->bandemprendimientosCreados = false;
    }

    public function  actualizarEmprendimientoBand($idEmprendimientoUpdate)
    {
        $this->banInfoEmprendimiento = false;
        $this->banCrearProducto = false;
        $this->banInfoEmprendimiento = false;
        $this->bandemprendimientosCreados = false;
        $this->banActualizarEmprendimiento = true;
        $this->idEmprendimiento = $idEmprendimientoUpdate;
        $emprendimientoUpdate = emprendimiento::where('id', $idEmprendimientoUpdate)
        ->first();

        $this->nombreEmprendimientoUpdate = $emprendimientoUpdate->nombre_emprendimiento;
        $this->descripcionEmprendimientoUpdate = $emprendimientoUpdate->descripcion;    
        
    }

    public function registrarProducto()
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln("registrarProducto:");
        $out->writeln($this->referencia);
        $out->writeln($this->nombre);
        $out->writeln($this->cantidad);
        $out->writeln($this->precio); 
        $out->writeln($this->descripcion);   

        $validarReferencia = productos::where('referencia',$this->referencia)
        ->where('idEmprendimiento',$this->idEmprendimientoNewProduct)
        ->first();
        if ($validarReferencia)
        {
            $out->writeln("Ya existe la referencia ingresada");
            session()->flash('messageCrearProducto','Error al crear el producto, la referencia ya existe...');
            return;
        }


        if ($this->imagenProducto) 
        {
            $out->writeln("registrarProducto Imagen");
            $filename = Str::slug($this->nombre) . '.' . $this->imagenProducto->extension();
            $path = $this->imagenProducto->storeAs('imagenes', $filename);
            //$cliente->logo = $path;
            $out->writeln("registrarProducto Imagen {$path}");          
           
        }
        else
        {
            $filename = 'default.jpg';   
        }

        
        $emprendimientoTipo = emprendimiento::where('id', $this->idEmprendimientoNewProduct)->first();

        $producto = productos::create([
            'nombre' => $this->nombre,
            'referencia' => $this->referencia,
            'cantidad' => $this->cantidad,
            'fotos' => $filename,
            'estado' => 1,
            'descripcion' => $this->descripcion,
            'precio' => $this->precio,
            'idEmprendimiento' => $this->idEmprendimientoNewProduct,
            'tipoEmprendimiento' => $emprendimientoTipo->tipo_emprendimiento,
            'respuestaAdmin' => 'Sin respuesta',
        ]);

        $this->nombre = '';
        $this->referencia = '';
        $this->cantidad = ''; 
        $filename = null; 
        $this->descripcion = ''; 
        $this->precio = ''; 
        $this->idEmprendimientoNewProduct= '';       

        //$emprendimientoNew = emprendimiento::where('id', $this->idEmprendimientoNewProduct)->first();            
        $out->writeln($emprendimientoTipo->nombre_emprendimiento);          
        $mailData = [
            'title' => 'Nueva solicitud de creacion de producto',
            'body' => 'El emprendedor '.$this->nameEmprendedor. ' ha solicitado la creacion de un nuevo producto asociado al emprendimiento: '.$emprendimientoTipo->nombre_emprendimiento
        ];
       
        $correoAdmin =  env('CORREO_ADMIN', 'emprendimientosucatolica@gmail.com');

        Mail::to($correoAdmin)->send(new RegistroEmprendedorMail($mailData));

        $this->estadoCancelarNewProducto();     
        $message9 = 'El producto se creó correctamente...';
        session()->flash('messageSuccessNewproduct',  $message9);

       

    }

    public function  estadoCancelarNewProducto()
    {
        $this->banCrearEmprendimiento = false;
        $this->banCrearProducto = false;        
        $this->banInfoEmprendimiento = true;
        $this->bandemprendimientosCreados = false;
        $this->banActualizarEmprendimiento = false;
        $this->banActualizarProducto = false;
        
    }

    public function  actualizarProducto($idProducto)
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln("actualizarProducto id:");
        $out->writeln($idProducto);
        $this->banInfoEmprendimiento = false;
        $this->banCrearProducto = false;
        $this->banInfoEmprendimiento = false;
        $this->bandemprendimientosCreados = false;
        $this->banActualizarEmprendimiento = false;
        $this->banActualizarProducto = true;

        $productos = productos::where('idEmprendimiento', $this->idEmprendimiento)
        ->where('id', $idProducto)
        ->first();

        $this->productoUpdate = $productos;

        
        $this->nombreUpdate = $productos->nombre;
        $this->cantidadUpdate = $productos->cantidad;
        $this->precioUpdate = $productos->precio;
        $this->descripcionUpdate =$productos->descripcion; 
    }

    public function registrarActualizacionProducto()
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $productoActualizar = productos::find($this->productoUpdate->id);
        $emprendimientoTipo = emprendimiento::where('id', $productoActualizar->idEmprendimiento)->first();

        $productoEmail = $productoActualizar->nombre;
        $out->writeln('imagen formulario');
            
        $out->writeln('imagen guardada');
        //$out->writeln($productoActualizar->fotos);
        if ($this->imagenProductoUpdate)
        {
            $out->writeln("Foto nueva");
            // Eliminar la imagen asociada del almacenamiento
            if ($productoActualizar->fotos) 
            {
                Storage::delete('imagenes/' . $productoActualizar->fotos);
                $out->writeln("Antigua Foto eliminada");
            }

            $out->writeln("registrarProducto Imagen");
            $filename = Str::slug($this->nombreUpdate) . '.' . $this->imagenProductoUpdate->extension();
            $path = $this->imagenProductoUpdate->storeAs('imagenes', $filename);
            //$cliente->logo = $path;
            $out->writeln("registrarActualizacionProducto Imagen {$path}");  
            
            $productoActualizar->update([         
                'nombre' => $this->nombreUpdate,
                'cantidad' => $this->cantidadUpdate,            
                'estado' => 1,
                'fotos' => $filename,
                'descripcion' => $this->descripcionUpdate,
                'precio' => $this->precioUpdate,         
            ]);
        }   
        else 
        {        
            $out->writeln("No selecciono nueva foto");
            $productoActualizar->update([         
                'nombre' => $this->nombreUpdate,   
                'cantidad' => $this->cantidadUpdate,            
                'estado' => 1,
                'descripcion' => $this->descripcionUpdate,
                'respuestaAdmin' => 'Sin respuesta',
                'precio' => $this->precioUpdate,         
            ]);
        }

        $out->writeln($emprendimientoTipo->nombre_emprendimiento);          
        $mailData = [
            'title' => 'Nueva solicitud de modificacion de producto',
            'body' => 'El emprendedor '.$this->nameEmprendedor. ' ha solicitado la modificacion del producto: '. $productoEmail .',  asociado al emprendimiento: '.$emprendimientoTipo->nombre_emprendimiento
        ];
       
        $correoAdmin =  env('CORREO_ADMIN', 'emprendimientosucatolica@gmail.com');

        Mail::to($correoAdmin)->send(new RegistroEmprendedorMail($mailData));
       
        $this->referenciaUpdate = '';
        $this->nombreUpdate ='';
        $this->cantidadUpdate = '';
        $this->precioUpdate = '';
        $this->descripcionUpdate =''; 
        $this->imagenProductoUpdate = '';
        $this->productoUpdate = '';
        $this->productoActualizar = '';

        $this->estadoCancelarNewProducto();         

        $message10 = 'El producto se actualizó correctamente...';
        session()->flash('messageSuccessUpdateproduct',  $message10);

    }

    public function seleccionarCotizacion($numerofactura)
    {
        $this->cotizacionesProductos = cotizaciones::where('numerofactura', $numerofactura)->get();
    }
    public function cotizacionEstadoCancelar()
    {
        $this->cotizacionesProductos = null;
    }

    public function cotizarProducto($numerofactura)
    {
        $cotizacionActualizar = cotizaciones::where('numerofactura', $numerofactura)->first();

        $cotizacionActualizar->update([   
            'estado' => 3,                   
        ]);
        $userCotizacion = Cliente::where('id', $cotizacionActualizar->idCliente)->first();
        $mailData = [
            'title' => 'Respuesta cotizacion',
            'body' => 'Señor@:  '.$userCotizacion->nombre. ' ya se encuentra disponible su cotizacion '. $numerofactura.' en la plataforma. '
        ];

        Mail::to($userCotizacion->correo)->send(new RegistroEmprendedorMail($mailData));

        $message15 = 'LA cotizacion se envio correctamente...';
        session()->flash('messageSuccessUpdateproduct',  $message15);
    }

    public function eliminarProducto($idProductoEliminar)
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln("eliminarProducto:");
        $out->writeln($this->idEmprendedor);
        $out->writeln($idProductoEliminar);
        
        $productoEliminar = productos::find($idProductoEliminar);
        if ($productoEliminar)
        {
            // Eliminar el producto de la base de datos
            $productoEliminar->delete();
    
            // Eliminar la imagen asociada del almacenamiento
            if ($productoEliminar->fotos) {
                Storage::delete('imagenes/' . $productoEliminar->fotos);
            }
            $message7 = 'El producto se eliminó correctamente...';
            session()->flash('messageSuccessDeletePro',  $message7);
            
        } else
        {
            $out->writeln("eliminarProducto:error no existe Id");
            $message5 = 'Error al eliminar el producto... verifique los datos ingresados';
            session()->flash('message',  $message5);
        }
        $this->estadoCancelar();              
        return redirect()->route('homeAdminEmpredimientos');        
    }


    public function cerrarSesion()
    {
        session()->forget('myData');
        return redirect('/registroEmprendedor');  
    }
    public function render()
    {     

        if ( $this->banInfoEmprendimiento)
        {
            $productos = productos::where('idEmprendimiento', $this->idEmprendimiento)
            ->where(function ($search) {
                $search->where('nombre', 'like', '%' . $this->search . '%')
                       ->orwhere('referencia', 'like', '%' . $this->search . '%');
            })
            ->orderby('fecha_creacion','desc')
            ->paginate(5);

        }
        else 
        {
            $productos = [];                     
        }  

        return view('livewire.emprendedores-admin-lv',[
            // 'entregas' =>  $entregas,
             'productos' =>  $productos,
 
         ]);      
       
    }
}
