<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Enums\PublicationSupport;
use App\Enums\PublicationFormat;
use App\Models\Publication;

class PublicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = Storage::get('publications.json');
        $data = json_decode($json);
        foreach ($data as $obj) {
            DB::table('publications')->insert([
                'name'             => $obj->name ?: "",
                'cycle'            => $obj->cycle ?: "",
                'cyclenum'         => $obj->indice ?: "",
                'isbn'             => $obj->isbn ?: "",
                'type'             => $obj->type_contenu ?: "",
                'support'          => $obj->support ?: PublicationSupport::PAPIER,
                'format'           => $obj->format ?: PublicationFormat::INCONNU,

                'is_verified'      => $obj->is_verified ?: "0",
                'verified_by'      => $obj->verified_by ?: "",
                'dl'               => $obj->dl ? StrDLAItoBDFI($obj->dl, $obj->approximate_parution) : "",
                'ai'               => $obj->ai ? StrDLAItoBDFI($obj->ai, $obj->approximate_parution) : "",
                'is_genre'         => $obj->hg,
                'genre_stat'       => $obj->genrestat,
                'target_audience'  => $obj->cible,
                'target_age'       => $obj->age,
                'dimensions'       => $obj->dim ?: "",
                'thickness'        => $obj->epaisseur ?: "",
                'printer'          => $obj->printer ?: "",
                'pages_dpi'        => $obj->dpi ?: '',  // dernière page imprimée
                'pages_dpu'        => $obj->dpu ?: '',  // dernière page charge utile
                'pagination'       => $obj->pto ?: '',  // pagination totale
                'printed_price'    => $obj->codeprix ?: "",

                'cover'                  => $obj->cover ?: "",
                'illustrators'           => $obj->illustrators ?: "",
                'approximate_parution'   => $obj->approximate_parution ?: "",
                'approximate_pages'      => $obj->pages ?: 0,
                'approximate_price'      => $obj->prix ?: "",


                'is_hardcover'       => $obj->relie,
                'has_dustjacket'     => $obj->jaquette,
                'has_coverflaps'     => 0,

                'publisher_id'     => $obj->id_ed,

                'cover_front'      => $obj->cover_front ?: '',
                'cover_back'       => $obj->cover_back ?: '',
                'cover_spine'      => "",
                'withband_front'   => $obj->withband_front ?: '',
                'withband_back'    => "",
                'withband_spine'   => "",
                'dustjacket_front'   => $obj->dustjacket_front ?: '',
                'dustjacket_back'    => "",
                'dustjacket_spine'   => "",

                'information'      => $obj->description ?: 0,
                'private'          => $obj->private ?: 0,

                'created_at'   => today(),
                'updated_at'   => today(),
                'deleted_at'   => NULL,

                'created_by'   => 1,
                'updated_by'   => 1,
                'deleted_by'   => NULL
            ]);
        }

        Publication::factory()
            ->count(20)
            ->create();
    }
}
