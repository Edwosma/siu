<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Cliente;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;


class AuthController extends Controller
{
    //
    public function registerUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'identification' => '23123132',
            'identification_type' => '1',
            'birthdate'=>'1989-07-27',
            'estado' => 1,
            'estado_registro' => 1,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['user' => $user, 'message' => 'Created successfully!'], 201);
    }

    public function loginUser(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'device_name' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return $user->createToken($request->device_name)->plainTextToken;
    }


    public function loginCliente(Request $request)
    {
        $request->validate([
            'correo' => 'required|string|email',
            'clave' => 'required|string',
            'device_name' => 'required',
        ]);

        $cliente = Cliente::where('correo', $request->correo)->first();

        if (! $cliente || ! Hash::check($request->clave, $cliente->clave)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return $cliente->createToken($request->device_name)->plainTextToken;
    }
}
