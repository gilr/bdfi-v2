<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class CycleTitleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = Storage::get('cyc_tit.json');
        $data = json_decode($json);
        foreach ($data as $obj) {
            DB::table('cycle_title')->insert([
                'cycle_id'  => $obj->id_cyc,
                'title_id'  => $obj->id_tit,
                'number'    => $obj->num,
                'order'     => $obj->order ?: 0,

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
