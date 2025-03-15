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
        $json = Storage::get('bdfiv2\bdfibasev2_table_countries.json');
        $data = json_decode($json);
        foreach ($data as $record) {
            DB::table('countries')->insert([
                'id'             => $record->id,

                'name'           => $record->name,
                'slug'           => $record->slug,
                    // SlugService::createSlug(Country::class, 'slug', $record->name),

                'nationality'    => $record->nationality,
                'code'           => $record->code,
                'internal_order' => $record->internal_order,

                'created_at'   => $record->created_at,
                'updated_at'   => $record->updated_at,
                'deleted_at'   => $record->deleted_at,

                // TBD si besoin de revoir
                'created_by'   => $record->created_by,
                'updated_by'   => $record->updated_by,
                'deleted_by'   => $record->deleted_by,
            ]);
        }
    }

}
