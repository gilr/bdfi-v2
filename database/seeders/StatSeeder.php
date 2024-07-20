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
        $json = Storage::get('bdfiv1\bdfibase_table_stats.json');
        $data = json_decode($json);
        foreach ($data as $record) {
            DB::table('stats')->insert([
                'id'            => $record->id,

                'date'          => $record->date,
                'authors'       => $record->auteurs,
                'series'        => $record->series,
                'references'    => $record->references,
                'novels'        => $record->romans,
                'short_stories' => $record->nouvelles,
                'collections'   => $record->recueils,
                'magazines'     => $record->revues,
                'essays'        => $record->essais,

                'created_at'  => $record->created_at,
                'updated_at'  => $record->updated_at,
                'deleted_at'  => NULL,

                // 99=>1 - 1=>2 - 2=>3 - 3=>4
                'created_by'  => 1,
                'updated_by'  => 1,
                'deleted_by'  => NULL
            ]);
        }
    }

}
