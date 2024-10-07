<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use \Cviebrock\EloquentSluggable\Services\SlugService;
use App\Models\Award;

class AwardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = Storage::get('awards.json');
        $data = json_decode($json);
        foreach ($data as $obj) {
            DB::table('awards')->insert([
                'name'          => $obj->name ?: "",
                'slug'          => SlugService::createSlug(Award::class, 'slug', $obj->name),

                'alt_names'     => $obj->alt_names ?: "",
                'year_start'    => $obj->year_start ?: "",
                'year_end'      => $obj->year_end ?: "",
//                'given_by'      => $obj->given_by ?: "",
                'given_for'     => $obj->given_for ?: "",
                'country_id'    => $obj->country_id ?: "",
                'url'           => $obj->url ?: "",
                'information'   => $obj->description ?: "",

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
