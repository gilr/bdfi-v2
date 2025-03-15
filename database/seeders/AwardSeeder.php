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
        $json = Storage::get('bdfiv2\bdfibasev2_table_awards.json');
        $data = json_decode($json);
        foreach ($data as $record) {
            DB::table('awards')->insert([
                'id'            => $record->id,
                'name'          => $record->name,
                'slug'          => $record->slug,
                                // SlugService::createSlug(Award::class, 'slug', $record->name),

                'alt_names'     => $record->alt_names,
                'year_start'    => $record->year_start,
                'year_end'      => $record->year_end,
                'given_for'     => $record->given_for,
                'country_id'    => $record->country_id,
                'url'           => $record->url,
                'information'   => $record->information,

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
