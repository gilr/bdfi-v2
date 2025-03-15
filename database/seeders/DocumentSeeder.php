<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = Storage::get('bdfiv2\bdfibasev2_table_documents.json');
        $data = json_decode($json);

        foreach ($data as $record) {
            DB::table('documents')->insert([
                'id'             => $record->id,
                'name'           => $record->name,
                'file'           => $record->file,
                'author_id'      => $record->author_id,

                'item_type'      => $record->item_type,
                'item_id'        => $record->item_id,

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
