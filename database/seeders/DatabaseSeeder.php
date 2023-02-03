<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Caixa;
use App\Models\Unidade;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        for($i = 6; $i <= 57; $i++) {
            DB::table('caixas')->insert([
                'numero' => $i,
                'status' => 'disponivel',
                'espaco_total' => 80,
                'espaco_ocupado' => 0,
                'espaco_disponivel' => 80,
                'predio_id' => 1,
                'andar_id' => Unidade::localizacaoAndar($i),
            ]);
        }
    }
}
