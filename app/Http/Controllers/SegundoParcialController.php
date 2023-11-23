<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SegundoParcialController extends Controller
{
    public function segundoParcial(){
        
        
        return view('parciales.segundoParcial', array(

        ));
    }

    public function uploadJsonFile(Request $request)
    {
        if ($request->hasFile('jsonFile')) {
            $file = $request->file('jsonFile');
            $filePath = $file->store('uploads'); // Guarda el archivo en la carpeta 'uploads' (puedes ajustarlo según tus necesidades)

            // Aquí puedes realizar cualquier procesamiento adicional que necesites

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'error' => 'No se recibió el archivo JSON'], 400);
    }

    public function uploadJsonFilePreguntas(Request $request)
    {
        if ($request->hasFile('jsonFilePreguntas')) {
            $file = $request->file('jsonFilePreguntas');
            $filePath = $file->store('uploads'); // Guarda el archivo en la carpeta 'uploads' (puedes ajustarlo según tus necesidades)

            // Aquí puedes realizar cualquier procesamiento adicional que necesites

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'error' => 'No se recibió el archivo JSON'], 400);
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
