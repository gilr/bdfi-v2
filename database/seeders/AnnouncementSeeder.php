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
        $json = Storage::get('bdfiv2\bdfibasev2_table_announcements.json');
        $data = json_decode($json);
        foreach ($data as $record) {
            DB::table('announcements')->insert([
                'id'          => $record->id,

                'date'        => $record->date,
                'type'        => $record->type,
                'name'        => $record->name,
                'information' => $record->information,
                'url'         => $record->url,

                'created_at'  => $record->created_at,
                'updated_at'  => $record->updated_at,
                'deleted_at'  => $record->deleted_at,

                // TBC si besoin d'ajuster'
                'created_by'  => $record->created_by,
                'updated_by'  => $record->updated_by,
                'deleted_by'  => $record->deleted_by,
            ]);
        }
    }

}
