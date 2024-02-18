<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class AuthorTitleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = Storage::get('aut_tit.json');
        $data = json_decode($json);
        foreach ($data as $obj) {
            DB::table('author_title')->insert([
                'author_id'  => $obj->id_aut,
                'title_id' => $obj->id_tit,
//                'number' => "",

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
