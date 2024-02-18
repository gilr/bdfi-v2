<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class AwardCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = Storage::get('categories.json');
        $data = json_decode($json);
        foreach ($data as $obj) {
            DB::table('award_categories')->insert([
                'name'           => $obj->name ?: "",
                'award_id'       => $obj->award_id ?: "",
                'internal_order' => $obj->internal_order ?: "",
                'type'           => $obj->type ?: "",
                'genre'          => $obj->genre ?: "",
                'subgenre'       => $obj->subgenre ?: "",
                'information'    => $obj->description ?: "",

                'created_at'     => today(),
                'updated_at'     => today(),
                'deleted_at'     => NULL,

                'created_by'     => 1,
                'updated_by'     => 1,
                'deleted_by'     => NULL
            ]);
        }
    }
}
