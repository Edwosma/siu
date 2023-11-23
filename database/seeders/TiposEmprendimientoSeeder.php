<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TiposEmprendimiento;

class TiposEmprendimientoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $tipo = new TiposEmprendimiento();
        $tipo->nombre = 'Tecnologia';
        $tipo->descripcion = 'Tecnologia';
        $tipo->save();

        $tipo = new TiposEmprendimiento();
        $tipo->nombre = 'Textil';
        $tipo->descripcion = 'Tecnologia';
        $tipo->save();

        $tipo = new TiposEmprendimiento();
        $tipo->nombre = 'Alimentos';
        $tipo->descripcion = 'Tecnologia';
        $tipo->save();
    }
}
