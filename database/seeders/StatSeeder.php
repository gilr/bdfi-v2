<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class StatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = Storage::get('bdfiv2\bdfibasev2_table_stats.json');
        $data = json_decode($json);

        foreach ($data as $record) {
            DB::table('stats')->insert([
                'id'            => $record->id,

                'date'          => $record->date,
                'authors'       => $record->authors,
                'series'        => $record->series,
                'references'    => $record->references,
                'novels'        => $record->novels,
                'short_stories' => $record->short_stories,
                'collections'   => $record->collections,
                'magazines'     => $record->magazines,
                'essays'        => $record->essays,

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
