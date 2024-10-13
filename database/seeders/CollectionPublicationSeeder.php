<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class CollectionPublicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = Storage::get('col_pub.json');
        $data = json_decode($json);
        foreach ($data as $obj) {
            DB::table('collection_publication')->insert([
                'collection_id'  => $obj->id_col,
                'publication_id' => $obj->id_pub,
                'order'          => $obj->order,
                'number'         => "$obj->num",

                'created_at'   => today(),
                'updated_at'   => today(),
                'deleted_at'   => NULL,

                'created_by'   => 1,
                'updated_by'   => 1,
                'deleted_by'   => NULL
            ]);

            // TODO : Champ spécifique version v2 bêta, pourra être supprimé ensuite
            DB::table('collections')
                ->where('id', $obj->id_col)
                ->update(['is_in_v2beta' => 1]);

        }

    }
}
