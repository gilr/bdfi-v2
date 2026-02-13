<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Models\Author;

class AwardWinnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = Storage::get('bdfiv2\bdfibasev2_table_award_winners.json');
        $data = json_decode($json);

        foreach ($data as $record) {
            DB::table('award_winners')->insert([
                'id'                => $record->id,
                'year'              => $record->year,
                'award_category_id' => $record->award_category_id,
                'position'          => $record->position,
                'name'              => $record->name,
                'title'             => $record->title,
                'author_id'         => $record->author_id,
                'author2_id'        => $record->author2_id,
                'author3_id'        => $record->author3_id,
                'vo_title'          => $record->vo_title,
                'title_id'          => $record->title_id,
                'information'       => $record->information,

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
