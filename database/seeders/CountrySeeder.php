<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $backup = DB::connection('mysql2')->table('pays')->get();
        foreach ($backup as $record) {
            DB::connection('mysql')->table('countries')->insert([
                'id'             => $record->id,

                'name'           => $record->nom,
                'nationality'    => $record->nationalite,
                'code'           => $record->code,
                'internal_order' => $record->ordre_interne,

                'created_at'     => $record->created_at,
                'updated_at'     => $record->updated_at,
                'deleted_at'     => NULL,

                // 99=>1 - 1=>2 - 2=>3 - 3=>4
                'created_by'     => 1,
                'updated_by'     => 1,
                'deleted_by'     => NULL
            ]);
        }
    }
}
