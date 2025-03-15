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
        $json = Storage::get('bdfiv2\bdfibasev2_table_websites.json');
        $data = json_decode($json);

        foreach ($data as $record) {
            DB::table('websites')->insert([
                'id'              => $record->id,

                'url'             => $record->url,
                'author_id'       => $record->author_id,
                'website_type_id' => $record->website_type_id,
                'country_id'      => $record->country_id,

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
