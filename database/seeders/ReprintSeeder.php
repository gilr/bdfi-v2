<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use \Cviebrock\EloquentSluggable\Services\SlugService;
use App\Models\Reprint;

class ReprintSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = Storage::get('reprints.json');
        $data = json_decode($json);
        foreach ($data as $obj) {
            DB::table('reprints')->insert([
                'slug'             => SlugService::createSlug(Reprint::class, 'slug', $obj->name_pub . " - Retirage " . StrDateformat($obj->approximate_parution)),

                'publication_id'   => $obj->id_pub,
                'ai'               => $obj->ai ? StrDLAItoBDFI($obj->ai, $obj->approximate_parution) : "",
                'approximate_parution'   => $obj->approximate_parution ?: "",

                'is_verified'      => $obj->is_verified ?: "0",
                'verified_by'      => $obj->verified_by ?: "",
                'information'      => "",
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