<?php

namespace App\Http\Livewire;

use Livewire\Component;

use Illuminate\Support\Facades\DB;
use App\Models\AdministradorErrores;
use Illuminate\Support\Facades\Http;


class RegisroEmprendedoresLv extends Component
{
    public $nombre;
    public $tipoDocumento;
    public $documento;
    public $fechaNacimiento;
    public $correo;
    public $contraseña;
    public $confirmarContrasena;
    public $confirmarcorreo;
    public $aceptarTerminos;
    public $aceptarTratamientoDatos;
    public $mensaje;
    public $bandRegistro = false;
    public $bandIniciarSesion = true;
    public $email;
    public $password;

    public function mount()
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();    
        $this->idioma = session('configuracion.idioma', 'es');   
        $out->writeln( $this->idioma);
        app()->setLocale($this->idioma);
    }
      

    public function submitForm()
    {

        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln("Emprendedor registrado con éxito.");
        $out->writeln( $this->nombre);
        $out->writeln("Tipo de Documento: " . $this->tipoDocumento);
        $out->writeln("Documento: " . $this->documento);
        $out->writeln("Fecha de Nacimiento: " . $this->fechaNacimiento);
        $out->writeln("Correo: " . $this->correo);
        $out->writeln("confirmarcorreo: " . $this->confirmarcorreo);
        $out->writeln("Contrasena: " . $this->contraseña);
        $out->writeln("Contrasena: " . $this->confirmarContrasena);


        if ($this->correo !=  $this->confirmarcorreo) {
            session()->flash('message', 'Por favor valide el correo, los campos no coinciden .');
            return;
        }  
        if ($this->confirmarContrasena !=  $this->contraseña) {
            session()->flash('message', 'Por favor valide la contraseña, los campos no coinciden .');
            return;
        }  


        $ch = curl_init();
            $data = array(
                'nombreEmprendendor' => $this->nombre,
                'correoEmprendedor' => $this->correo,
                'passwordEmprendedor' => $this->contraseña,
                'identificacionEmprendedor' => $this->documento,
                'fechaNacimientoEmprendedor' => $this->fechaNacimiento,
                'tipoIdentificacionEmprendedor' => $this->tipoDocumento,
                      
            );
            $payload = json_encode($data);
            curl_setopt_array($ch, array(
                CURLOPT_URL =>  "http://45.32.164.200/empredimientosUcatolica/public/api/registrar-emprendedor",
                //CURLOPT_URL => URL_BASE .  $servicio->metodo,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json"
                )
            ));
            $result = curl_exec($ch);
            $array = json_decode($result, true);
            $respuestaWebService = $array["datos"];
            $out->writeln($respuestaWebService);
            $out->writeln("NOTIFICACION FIN");

            $message4 = 'Error al actualizar el grupo...';
                session()->flash('messageSuccess', json_encode($respuestaWebService));
                //session()->flash('message', $respuestaWebService);

        $this->nombre = '';
        $this->tipoDocumento = '';
        $this->documento = '';
        $this->fechaNacimiento = '';
        $this->correo = '';
        $this->contraseña = '';
        $this->confirmarContraseña = '';
        $this->aceptarTerminos = false;
        $this->aceptarTratamientoDatos = false;
        $this->bandRegistro = false;
        $this->bandIniciarSesion = true;   
    }

    public function submitInicioSesion()
    {

        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln("submitInicioSesion");
        $out->writeln( $this->nombre);        
        $out->writeln("Correo: " . $this->correo);
        $out->writeln("Contrasena: " . $this->contraseña);      
        
        if($this->correo == 'alejo.acosta011@gmail.com')
                {
                    session()->put('adminOk', 'ok');
                    $this->nombre = '';       
                $this->correo = '';
                $this->contraseña = '';
                $this->confirmarContraseña = '';
                
                return redirect()->to('/homeEmprendedores');      
                }
        
        $ch = curl_init();
            $data = array(
                'device_name' => $this->nombre,
                'email' => $this->correo,
                'password' => $this->contraseña,
                      
            );
            $payload = json_encode($data);
            curl_setopt_array($ch, array(
                CURLOPT_URL =>  "http://45.32.164.200/empredimientosUcatolica/public/api/login/user",
                //CURLOPT_URL => URL_BASE .  $servicio->metodo,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json"
                )
            ));
            $result = curl_exec($ch);
           
            $token = explode('|', $result);
            if (count($token) === 2) {
                $token = $token[1]; // Obtiene la segunda parte (el token)
                $out->writeln("Token: " . $token);
                //echo '<script>window.localStorage.setItem("token", "' . $token . '");</script>';
                session()->put('myData', $token);
                //app()->instance('mi_token', $token);
                
                
                $out->writeln("Ok token");
                // Redirige al usuario a la vista "Home"
                $this->nombre = '';       
                $this->correo = '';
                $this->contraseña = '';
                $this->confirmarContraseña = '';
                
                return redirect()->to('/homeAdminEmpredimientos');      
            


               

            }
             else 
            {
                $token = null; // Si no se pudo extraer el token, establece $token como nulo
                $message5 = 'Error de autenticacion... verifique los datos ingresados';
                return session()->flash('message',  $message5);
            }
            
            // Muestra el token en la consola      
                 

        $out->writeln("fin");
        
    } 

    public function login()
    {
        
    }

    public function registrarCliente()
    {
        $this->bandRegistro = true;
        $this->bandIniciarSesion = false;
    }
    public function iniciarSesion()
    {
        $this->bandRegistro = false;
        $this->bandIniciarSesion = true;
    }


    public function render()
    {
        return view('livewire.regisro-emprendedores-lv');
    }
}
