<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Enums\QualityStatus;
use \Cviebrock\EloquentSluggable\Services\SlugService;
use App\Models\Author;

class AuthorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = Storage::get('bdfiv2\bdfibasev2_table_authors.json');
        $data = json_decode($json);

        foreach ($data as $record) {
            DB::table('authors')->insert([
                'id'           => $record->id,

                'name'         => $record->name,
                'nom_bdfi'     => $record->nom_bdfi,
                'first_name'   => $record->first_name,
                'slug'         => $record->slug,
                                    // SlugService::createSlug(Author::class, 'slug', ($record->first_name == "" ? $record->name : sanitizeFirstName($record->first_name) . " " . $record->name)),

                'is_pseudonym' => $record->is_pseudonym,
                'legal_name'   => $record->legal_name,
                'alt_names'    => $record->alt_names,
                'gender'       => $record->gender,
                'birth_date'   => $record->birth_date,
                'birthplace'   => $record->birthplace,
                'date_death'   => $record->date_death,
                'place_death'  => $record->place_death,
                'information'  => $record->information,
                'private'      => $record->private,
                'is_visible'   => $record->is_visible,

                'country_id'   => $record->country_id,
                'country2_id'  => $record->country2_id,
                'quality'      => $record->quality,

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
