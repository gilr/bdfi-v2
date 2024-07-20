<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class SignatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = Storage::get('bdfiv1\bdfibase_table_pseudonymes.json');
        $data = json_decode($json);
        foreach ($data as $record) {
            DB::table('signatures')->insert([
                'id'           => $record->id,

                'author_id'    => $record->auteur_id,
                'signature_id' => $record->pseudo_id,

                'created_at'   => ($record->created_at !== "0000-00-00 00:00:00") ? $record->created_at : "2014-10-01 00:00:00",
                'updated_at'   => $record->updated_at,
                'deleted_at'   => NULL,

                // 99=>1 - 1=>2 - 2=>3 - 3=>4
                'created_by'   => 1,
                'updated_by'   => 1,
                'deleted_by'   => NULL
            ]);
        }
    }

}
