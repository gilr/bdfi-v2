<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Enums\CycleType;
use App\Enums\QualityStatus;

class CycleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = Storage::get('cycles.json');
        $data = json_decode($json);
        foreach ($data as $obj) {
            DB::table('cycles')->insert([
                'name'         => $obj->name ?: "",
                'nom_bdfi'     => $obj->nom_bdfi ?: "",
                'alt_names'    => $obj->alt_names ?: "",
                'vo_names'     => $obj->vo_names ?: "",
                'type'         => CycleType::CYCLE->value,

                'parent_id'    => NULL,

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
        foreach ($data as $obj) {
            DB::table('cycles')->updateOrInsert(
                ['nom_bdfi'     => $obj->nom_bdfi],
                ['parent_id'    => $obj->parent ?: NULL]
            );
        }
    }
}
