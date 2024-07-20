<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class WebsiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = Storage::get('bdfiv1\bdfibase_table_url_auteur.json');
        $data = json_decode($json);
        foreach ($data as $record) {
            DB::table('websites')->insert([
                'id'              => $record->id,

                'url'             => $record->url,
                'author_id'       => $record->auteur_id,
                'website_type_id' => $record->type_site_id,
                'country_id'      => $record->langue_id,

                'created_at'      => $record->created_at,
                'updated_at'      => $record->updated_at,
                'deleted_at'      => NULL,

                // 99=>1 - 1=>2 - 2=>3 - 3=>4
                'created_by'      => 1,
                'updated_by'      => 1,
                'deleted_by'      => NULL
            ]);
        }
    }

}
