<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use \Cviebrock\EloquentSluggable\Services\SlugService;
use App\Models\Country;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = Storage::get('bdfiv1\bdfibase_table_pays.json');
        $data = json_decode($json);
        foreach ($data as $obj) {
            DB::table('countries')->insert([
                'id'             => $obj->id,

                'name'           => $obj->nom,
                'slug'             => SlugService::createSlug(Country::class, 'slug', $obj->nom),

                'nationality'    => $obj->nationalite,
                'code'           => $obj->code,
                'internal_order' => $obj->ordre_interne,

                'created_at'     => $obj->created_at,
                'updated_at'     => $obj->updated_at,
                'deleted_at'     => NULL,

                // 99=>1 - 1=>2 - 2=>3 - 3=>4
                'created_by'     => 1,
                'updated_by'     => 1,
                'deleted_by'     => NULL
            ]);
        }
    }

}
