<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = Storage::get('bdfiv1\bdfibase_table_annonces.json');
        $data = json_decode($json);
        foreach ($data as $record) {
            DB::table('announcements')->insert([
                'id'          => $record->id,

                'date'        => $record->date,
                'type'        => $record->type,
                'name'        => $record->sujet,
                'information' => $record->description,
                'url'         => $record->url,

                'created_at'  => $record->created_at,
                'updated_at'  => $record->updated_at,
                'deleted_at'  => NULL,

                // 99=>1 - 1=>2 - 2=>3 - 3=>4
                'created_by'  => ($record->user_id == 99 ? 1 : $record->user_id + 1),
                'updated_by'  => ($record->user_id == 99 ? 1 : $record->user_id + 1),
                'deleted_by'  => NULL
            ]);
        }
    }

}
