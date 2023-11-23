<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClientesAdminController extends Controller
{     
    public function homeClienteAdmin()
    {        
        return view('clientes.clientesAdmin', array(
    ));
    }
}
