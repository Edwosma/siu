<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EmprendedoresController extends Controller
{
    public function homeEmprendedores()
    {
        
        return view('emprendedores.emprendedoresHome', array(

        ));
    }

    public function gestionEmprendedores()
    {
        
        return view('emprendedores.gestionEmprendedores', array(

        ));
    }

    public function registroEmprendedor()
    {
        
        return view('emprendedores.registroEmprendedores', array(

        ));
    }
    public function registrarEmprendedor(Request $request)
    {
        // Valida y guarda los datos del emprendedor
        $data = $request->validate([
            'nombreEmprendendor' => 'required|string',
            'correoEmprendedor' => 'required|email',
            'passwordEmprendedor' => 'required|string',
            'identificacionEmprendedor' => 'required|string',
            'fechaNacimientoEmprendedor' => 'required|date',
            'tipoIdentificacionEmprendedor' => 'required|integer',
        ]);

        // Realiza la lógica para guardar el emprendedor en la base de datos (si es necesario).

        // Realiza la solicitud POST al servidor remoto
        $response = Http::post('http://45.32.164.200/berakha/public/api/registrar-emprendedor', $data);

        // Verifica si la solicitud fue exitosa
        if ($response->successful()) {
            // Puedes manejar la respuesta del servidor remoto aquí si es necesario
            return $response->json();
        } else {
            // Si la solicitud no fue exitosa, puedes manejar errores o retornar una respuesta de error.
            return response()->json(['error' => 'Error al registrar el emprendedor'], $response->status());
        }
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
