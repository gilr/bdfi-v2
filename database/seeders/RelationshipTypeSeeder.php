<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class RelationshipTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = Storage::get('bdfiv1\bdfibase_table_types_lien.json');
        $data = json_decode($json);
        foreach ($data as $record) {
            DB::table('relationship_types')->insert([
                'id'                   => $record->id,

                'name'                 => $record->nom . "-" . $record->inverse,
                'relationship'         => $record->nom,
                'reverse_relationship' => $record->inverse,

                'created_at'           => $record->created_at,
                'updated_at'           => $record->updated_at,
                'deleted_at'           => NULL,

                // 99=>1 - 1=>2 - 2=>3 - 3=>4
                'created_by'           => 1,
                'updated_by'           => 1,
                'deleted_by'           => NULL
            ]);
        }
    }

}
