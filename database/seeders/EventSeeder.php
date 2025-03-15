<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use \Cviebrock\EloquentSluggable\Services\SlugService;
use App\Models\Event;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = Storage::get('bdfiv2\bdfibasev2_table_events.json');
        $data = json_decode($json);

        foreach ($data as $record) {

            DB::table('events')->insert([
                'id'          => $record->id,

                'name'        => $record->name,
                'slug'        => $record->slug,
                                // SlugService::createSlug(Event::class, 'slug', $record->sujet),
                'type'        => $record->type,
                'start_date'  => $record->start_date,
                'end_date'    => $record->end_date,
                'place'       => $record->place,
                'information' => $record->information,
                'url'         => $record->url,

                'is_confirmed'     => $record->is_confirmed,
                'is_full_scope'    => !$record->is_full_scope,
                'publication_date' => $record->publication_date,

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

