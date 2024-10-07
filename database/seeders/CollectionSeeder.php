<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Enums\CollectionSupport;
use App\Enums\QualityStatus;
use \Cviebrock\EloquentSluggable\Services\SlugService;
use App\Models\Collection;

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
                'slug'             => SlugService::createSlug(Collection::class, 'slug', $obj->name),

                'shortname'        => $obj->subcoll == "" ? $obj->name : $obj->subcoll,
                'sigle_bdfi'       => $obj->sigle ?: "",
                'alt_names'        => $obj->alt_names ?: "",

                'publisher_id'     => $obj->id_ed,
                'publisher2_id'    => $obj->id_ed2 === "0" ? NULL : $obj->id_ed2,
                'publisher3_id'    => $obj->id_ed3 === "0" ? NULL : $obj->id_ed3,
                'parent_id'        => $obj->id_parent ?: NULL,
                'year_start'       => $obj->creation ?: 0,
                'year_end'         => $obj->fin ?: NULL,

                'support'          => $obj->support ?: CollectionSupport::PAPIER,
                'type'             => $obj->type ?: "collection",
                'periodicity'      => $obj->periodicite ?: "n-a",
                'format'           => $obj->format ?: NULL,
                'dimensions'       => $obj->dimensions ?: "",
                'cible'            => $obj->cible ?: NULL,
                'genre'            => $obj->genre ?: NULL,

                'forum_topic_id'   => $obj->forum_topic_id ?: 0,
                'information'      => $obj->description ?: "",
                'private'          => $obj->private ?: "",
                'quality'          => QualityStatus::VIDE,

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
