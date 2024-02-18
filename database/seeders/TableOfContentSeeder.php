<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class TableOfContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = Storage::get('pub_tit.json');
        $data = json_decode($json);
        foreach ($data as $obj) {
            DB::table('table_of_content')->insert([
                'publication_id'  => $obj->id_pub,
                'title_id'        => $obj->id_tit,

                'order'        => $obj->ordre,
                'level'        => $obj->niveau,
                'start_page'   => $obj->debut,
                'end_page'     => NULL,

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
