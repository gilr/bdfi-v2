<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ReOrderCollections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bdfi:re-order-collections {file=collect.id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renumber the ordering numbers of a list of collections';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');
        $this->info("Start of command Bdfi:re-order-collections [file = $file]");

        if (!Storage::exists($file))
        {
            $this->info("Error: file $file does not exist in the app storage directory");
            exit;
        }
        $fileHandle = Storage::readStream($file);

        while ($collection = fgets ($fileHandle)) {
            //$this->info("Traitement ligne : $variant");

            if ($collection[0] == "#") {
                continue;
            }
            $this->handle_collection($collection);
        }

        $this->info('End of command Bdfi:re-order-collections');
    }

    /**
     * Traite une ligne du fichier collection (un ID)
     */
    public function handle_collection(string $line)
    {
        list($cid, $name) = explode ("\t", $line);

        $publicationsPivot = DB::table('collection_publication')
            ->where('collection_id', $cid)
            ->orderBy('order') // Pour conserver l'ordre existant
            ->get();

        $order = 1;
        foreach ($publicationsPivot as $pivot) {
            DB::table('collection_publication')
                ->where('id', $pivot->id)
                ->update(['order' => $order]);
            $order++;
        }

    }
}
