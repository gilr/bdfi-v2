<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Title;
use App\Models\Author;

class BdfiAddVariantsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bdfi:add-variants {file=variants.json}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a list of variant titles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');
        $this->info("Start of command Bdfi:add-variants [file = $file]");

        if (!Storage::exists($file))
        {
            $this->info("Error: file $file does not exist in the app storage directory");
            exit;
        }
        $variantsHandle = Storage::readStream($file);

        while ($variant = fgets ($variantsHandle)) {
            //$this->info("Traitement ligne : $variant");

            if ($variant[0] == "#") {
                continue;
            }
            $this->handle_variant($variant);
        }

        $this->info('End of command Bdfi:add-variants');
    }

    /**
     * Traite une ligne du fichier variants
     */
    public function handle_variant(string $line)
    {
        list($type, $premier, $autp, $tradp, $variant, $autv, $tradv, $typevariant) = explode ("\t", $line);
        if ($type == "R") { $dbtype = "novel"; }
        elseif ($type == "r") { $dbtype = "novella"; }
        elseif ($type == "N") { $dbtype = "shortstory"; }
        else {
            $this->info("Error: type = $type is an unknown length type");
            return;
        }

        $typevariant = rtrim($typevariant);
//            $this->info("[$premier] -> [$variant]");

        // Trouver ID de l'auteur premier - Pas trouvé, on sort
        if (!$AP=Author::where('nom_bdfi', $autp)->first())
        {
            $this->info("Error: nom_bdfi = $autp does not exist in the author table");
            return;
        }
        $id_autp = $AP->id;

        // Trouver ID de l'auteur variant - Pas trouvé, on sort
        if (!$AV=Author::where('nom_bdfi', $autv)->first())
        {
            $this->info("Error: nom_bdfi = $autv does not exist in the author table");
            return;
        }
        $id_autv = $AV->id;

        // Chercher ID du titre premier - Pas trouvé, on sort (pour l'instant)
        if (!$TP=$AP->titles()->whereRaw('name COLLATE utf8mb4_bin = (?)', $premier)->where('type', $dbtype)->where('translators', $tradp)->first())
//      if (!$TP=$AP->titles()->where('name', $premier)->where('type', $dbtype)->where('translators', $tradp)->first())
        {
            $this->info("Error: title [$premier][$tradp][$dbtype] does not exist in the title table for this author");
            return;

            // Après : Pas trouvé, on créé
        }
        else
        {
            $id_premier = $TP->id;
//          $this->info("OK! title [$premier] exists, id $id_premier");
        }

        // Chercher ID du titre variant - Pas trouvé, on sort (pour l'instant)
        if (!$TV=$AV->titles()->whereRaw('name COLLATE utf8mb4_bin = (?)', $variant)->where('type', $dbtype)->where('translators', $tradv)->first())
//            if (!$TV=$AV->titles()->where('name', $variant)->where('type', $dbtype)->where('translators', $tradv)->first())
        {
            $this->info("Error: title [$variant][$tradv][$dbtype] does not exist in the title table for this author");
            return;

            // Après : Pas trouvé, on créé
        }
        else
        {
            $id_variant = $TV->id;
//                $this->info("OK! title [$variant] exists, id $id_variant");
        }

        // on ajoute le lien, premier est le parent de variant
        $affected = DB::table('titles')->where('id', $id_variant)->update([
            'parent_id' => $id_premier,
            'variant_type' => "$typevariant"
        ]);
//      $this->info("$affected row updated");

    }
}
