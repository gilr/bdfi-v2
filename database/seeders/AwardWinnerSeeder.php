<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Models\Author;

class AwardWinnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = Storage::get('winners.json');
        $data = json_decode($json);
        foreach ($data as $obj) {

            $author_id = NULL;
            $author2_id = NULL;
            $author3_id = NULL;
            $aut = Author::where('nom_BDFI', $obj->author)->first();
            if ($aut) { $author_id = $aut->id; }
            $aut = Author::where('nom_BDFI', $obj->author2)->first();
            if ($aut) { $author2_id = $aut->id; }
            $aut = Author::where('nom_BDFI', $obj->author3)->first();
            if ($aut) { $author3_id = $aut->id; }

            DB::table('award_winners')->insert([
                'year'              => ($obj->year == "2010bis" ? "2010" : $obj->year),
                'award_category_id' => $obj->award_category_id,
                'position'          => $obj->position,
                'name'              => $obj->auteurs,
                'title'             => $obj->title,
                'author_id'         => $author_id,
                'author2_id'        => $author2_id,
                'author3_id'        => $author3_id,
                'vo_title'          => $obj->vo_title,
                'title_id'          => NULL,
                'information'       => $obj->note,

                'created_at'     => today(),
                'updated_at'     => today(),
                'deleted_at'     => NULL,

                'created_by'     => 1,
                'updated_by'     => 1,
                'deleted_by'     => NULL
            ]);
        }
    }
}
