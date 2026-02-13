<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use \Cviebrock\EloquentSluggable\Services\SlugService;
use App\Models\AwardCategory;

class AwardCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = Storage::get('bdfiv2/bdfibasev2_table_award_categories.json');
        $data = json_decode($json);

        foreach ($data as $record) {
            DB::table('award_categories')->insert([
                'id'            => $record->id,
                'name'          => $record->name,
                'slug'          => $record->slug,
                                // SlugService::createSlug(AwardCategory::class, 'slug', $record->name),

                'award_id'       => $record->award_id,
                'internal_order' => $record->internal_order,
                'type'           => $record->type,
                'genre'          => $record->genre,
                'subgenre'       => $record->subgenre,
                'information'    => $record->information,

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
