<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Models\Author;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Trouver ID de l'auteur premier - Pas trouvé, on sort
        $SC=Author::where('nom_bdfi', 'CALVO Sabrina')->first();
        $AS=Author::where('nom_bdfi', 'SPRAUEL Alain')->first();


        DB::table('documents')->insert([
                'name'         => "Bibliographie de Sabrina Calvo, par Alain Sprauel",
                'file'         => "documents/2019 BIB Calvo ICO v3.pdf",
                'author_id'    => $AS->id,

                'item_type'    => 'App\Models\Author',
                'item_id'      => $SC->id,

                'created_at'   => today(),
                'updated_at'   => today(),
                'deleted_at'   => NULL,

                'created_by'   => 1,
                'updated_by'   => 1,
                'deleted_by'   => NULL
            ]);
    }
}
