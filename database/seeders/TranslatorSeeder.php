<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class TranslatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = Storage::get('traduct.json');
        $data = json_decode($json);
        foreach ($data as $obj) {
            DB::table('translators')->insert([
// Il faut mettre l'ID comme pour les auteurs ==> créer un traduct.id
// et faire les liens dans getouv, pour les pubs (illustrateurs) et les titres (traducteurs)
                'id'           => $obj->id,

                'name'         => $obj->name ?: "",
                'information'  => "",
                'private'      => "",

                'created_at'   => today(),
                'updated_at'   => today(),
                'deleted_at'   => NULL,

                'created_by'   => 1,
                'updated_by'   => 1,
                'deleted_by'   => NULL
            ]);
        }
    }
}
