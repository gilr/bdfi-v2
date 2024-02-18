<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Enums\IsNovelization;

class TitleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = Storage::get('titles.json');
        $data = json_decode($json);
        foreach ($data as $obj) {
            DB::table('titles')->insert([
                'name'             => $obj->name ?: "",

                'type'            => $obj->type,
                'copyright_fr'    => $obj->copyright_fr,
                'copyright'       => $obj->copyright,
                'title_vo'        => $obj->vo,
                'pub_vo'          => "",
                'translators'     => $obj->trad,
                'parent_id'       => NULL, // Par défaut
                'variant_type'    => $obj->variant_type,

                'is_novelization'  => IsNovelization::NON->value,
                'is_visible'       => "1",
                'is_serial'        => $obj->feuilleton,
                'is_fullserial'    => $obj->serial_complet,
                'serial_info'      => $obj->serial_data,
                'is_genre'         => $obj->hg,
                'genre_stat'       => $obj->genrestat,
                'target_audience'  => $obj->cible,
                'target_age'       => $obj->age,

                'information'      => $obj->description ?: 0,
                'private'          => "",

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
