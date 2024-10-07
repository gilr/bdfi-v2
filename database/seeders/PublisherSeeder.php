<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Enums\PublisherType;
use App\Enums\QualityStatus;
use \Cviebrock\EloquentSluggable\Services\SlugService;
use App\Models\Publisher;

class PublisherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = Storage::get('editeurs.json');
        $data = json_decode($json);
        foreach ($data as $obj) {
            DB::table('publishers')->insert([
                'name'         => $obj->name ?: "",
                'slug'         => SlugService::createSlug(Publisher::class, 'slug', $obj->name),
                'alt_names'    => "",
                'country_id'   => $obj->pays,
                'year_start'   => $obj->creation ?: 0,
                'year_end'     => $obj->fin ?: NULL,
                'type'         => $obj->type ?: PublisherType::EDITEUR->value,
                'sigle_bdfi'   => $obj->sigle ?: "",

                'address'      => $obj->localisation ?: "",
                'information'  => "",
                'private'      => "",
                'quality'      => QualityStatus::VIDE,

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
