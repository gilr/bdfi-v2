<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Enums\QualityStatus;

class AuthorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = Storage::get('bdfiv1\bdfibase_table_auteurs.json');
        $data = json_decode($json);
        foreach ($data as $record) {
            DB::table('authors')->insert([
                'id'           => $record->id,

                'name'         => $record->nom,
                'nom_bdfi'     => $record->nom_bdfi,
                'first_name'   => $record->prenom,
                'is_pseudonym' => $record->pseudo,
                'legal_name'   => $record->nom_legal,
                'alt_names'    => $record->formes_nom,
                'gender'       => $record->sexe,
                'birth_date'   => $record->date_naiss,
                'birthplace'   => $record->lieu_naiss,
                'date_death'   => $record->date_deces,
                'place_death'  => $record->lieu_deces,
                'information'  => $record->bio,
                'private'      => $record->work_priv,
                'is_visible'   => 1,

                'country_id'   => $record->pays_id,
                'country2_id'  => $record->pays2_id,
                'quality'      => ($record->avancement_id == 5 ? QualityStatus::VALIDE :
                                    ($record->avancement_id == 4 ? QualityStatus::TERMINE :
                                    ($record->avancement_id == 3 ? QualityStatus::ACCEPTABLE :
                                    ($record->avancement_id == 2 ? QualityStatus::MOYEN :
                                    ($record->avancement_id == 1 ? QualityStatus::EBAUCHE :
                                    ($record->avancement_id == 9 ? QualityStatus::A_REVOIR :
                                    QualityStatus::VIDE)))))),

                'created_at'   => $record->created_at,
                'updated_at'   => $record->updated_at,
                'deleted_at'   => NULL,

                // 99=>1 - 1=>2 - 2=>3 - 3=>4
                'created_by'   => ($record->user_id == 99 ? 1 : $record->user_id + 1),
                'updated_by'   => ($record->user_id == 99 ? 1 : $record->user_id + 1),
                'deleted_by'   => NULL
            ]);
        }
    }

}
