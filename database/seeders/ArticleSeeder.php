<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Models\Collection;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $json = Storage::get('articles.json');
        $data = json_decode($json);
        foreach ($data as $obj) {

            $item = Collection::where('name', $obj->collection)->first();
            if ($item) {
                $item_id = $item->id;

                DB::table('articles')->insert([
                    'item_type'       => 'App\Models\Collection',
                    'item_id'         => $item_id,
                    'content'         => $obj->content,

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
}
