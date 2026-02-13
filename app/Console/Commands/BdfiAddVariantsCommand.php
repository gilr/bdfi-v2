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
    //protected $signature = 'bdfi:add-variants {file=variants.json}';
    protected $signature = 'bdfi:add-variants
    {file=variants.json}
    {--dry-run : Do not update the database}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a list of variant titles';

    protected int $linesTotal = 0;
    protected int $linesProcessed = 0;

    protected bool $dryRun = false;

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $this->dryRun = $this->option('dry-run');

        if ($this->dryRun) {
            $this->warn('DRY RUN MODE — no database update will be performed');
        }

        $file = $this->argument('file');
        $this->info("Start of command Bdfi:add-variants [file = $file]");

        if (!Storage::exists($file))
        {
            $this->info("Error: file $file does not exist in the app storage directory");
            return self::FAILURE;
        }
        $variantsHandle = Storage::readStream($file);

        while (($line = fgets ($variantsHandle)) !== false) {

            // Commentaires
            if (str_starts_with($line, "#")) {
                continue;
            }
            $this->linesTotal++;

            if ($this->handleVariant($line)) {
                $this->linesProcessed++;
            }
        }

        fclose($variantsHandle);
        $this->newLine();
        $this->info('End of command bdfi:add-variants');
        $this->line("Lines read      : {$this->linesTotal}");
        $this->line("Lines processed : {$this->linesProcessed}");

        if ($this->dryRun) {
            $this->warn('Dry-run mode: no database changes were made');
        }

        return self::SUCCESS;
    }

    /**
     * Traite une ligne du fichier variants
     */
    protected function handleVariant(string $line)
    {
        list($type, $premier, $autp, $tradp, $variant, $autv, $tradv, $typevariant) = explode ("\t", trim($line));

        $dbtype = match ($type) {
            'R' => 'novel',
            'r' => 'novella',
            'N' => 'shortstory',
            'O' => 'omnibus',
            'A' => 'anthologie',
            'C' => 'collection',
            'E' => 'essai',
            default => null,
        };
        if (!$dbtype) {
//            $this->warn("Unknown length type: {$type}");
            return false;
        }

        // Trouver ID de l'auteur premier - Pas trouvé, on sort
        $AP=Author::where('nom_bdfi', $autp)->first();
        if (!$AP)
        {
//            $this->warn("Error: nom_bdfi = $autp (premier) does not exist in the author table");
            return false;
        }

        // Trouver ID de l'auteur variant - Pas trouvé, on sort
        $AV=Author::where('nom_bdfi', $autv)->first();
        if (!$AV)
        {
//            $this->warn("Error: nom_bdfi = $autv (variant) does not exist in the author table");
            return false;
        }

        // Chercher ID du titre premier - Pas trouvé, on sort
        $TP = $AP->titles()->whereRaw('name COLLATE utf8mb4_unicode_ci = (?)', $premier)->where('type', $dbtype)->where('translators', $tradp)->first();
        if (!$TP)
        {
//            $this->warn("Error: title premier [$premier][$tradp][$dbtype] does not exist in the title table for this author");
            return false;
        }

        // Chercher ID du titre variant - Pas trouvé, on sort
        $TV = $AV->titles()->whereRaw('name COLLATE utf8mb4_unicode_ci = (?)', $variant)->where('type', $dbtype)->where('translators', $tradv)->first();
        if (!$TV)
        {
//            $this->warn("Error: title variant [$variant][$tradv][$dbtype] does not exist in the title table for this author");
            return false;
        }

        if ($this->dryRun) {
            // on considère la ligne comme "traitée"
            $this->line("DRY-RUN: would link variant [$typevariant]: [$variant][$tradv] → parent [$premier][$tradp]");
            return true;
        }

        $this->info("SUCCESS !! [$dbtype] title variant [$variant][$tradv] of title premier [$premier][$tradp]");

        // on ajoute le lien, premier est le parent de variant
        $affected = DB::table('titles')->where('id', $TV->id)->update([
            'parent_id' => $TP->id,
            'variant_type' => "$typevariant"
        ]);

        return true;
    }
}
