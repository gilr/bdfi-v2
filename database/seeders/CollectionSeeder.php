<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Enums\CollectionSupport;
use App\Enums\QualityStatus;

class CollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = Storage::get('collections.json');
        $data = json_decode($json);
        foreach ($data as $obj) {
            DB::table('collections')->insert([
                'name'             => $obj->name ?: "",
                'shortname'        => $obj->subcoll == "" ? $obj->name : $obj->subcoll,
                'sigle_bdfi'       => $obj->sigle ?: "",
                'alt_names'        => "",

                'publisher_id'     => $obj->id_ed,
                'publisher2_id'    => NULL,
                'publisher3_id'    => NULL,
                'parent_id'        => $obj->id_parent ?: NULL,
                'year_start'       => $obj->creation ?: 0,
                'year_end'         => $obj->fin ?: NULL,

                'support'          => $obj->support ?: CollectionSupport::PAPIER,
                'type'             => $obj->type ?: "collection",
                'format'           => $obj->format ?: NULL,
                'dimensions'       => "",
                'cible'            => $obj->cible ?: NULL,
                'genre'            => $obj->genre ?: NULL,

                'information' => "",
                'private'     => "",
                'quality'     => QualityStatus::VIDE,

                'created_at'   => today(),
                'updated_at'   => today(),
                'deleted_at'   => NULL,

                'created_by'   => 1,
                'updated_by'   => 1,
                'deleted_by'   => NULL
            ]);
        }
    }
}
