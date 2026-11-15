<?php

namespace App\Console\Commands;

use App\Bdfi\Import\BookImportPreparer;
use App\Bdfi\Import\BookLookup;
use App\Enums\IsNovelization;
use App\Models\Title;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * Réparation ponctuelle : TitleSeeder a forcé titles.is_novelization à "non" pour tous les titres.
 * Relit un fichier source (COL...), retrouve en base chaque texte marqué comme novellisation
 * (même règle que l'import : titre, titre VO, traducteurs, auteurs) et signale ceux restés à "non".
 *
 * Sans option : rapport seul (+ <nom>.novelizations.csv).
 * --sql   : écrit <nom>.novelizations.sql, à jouer sur une autre base (production) : chaque UPDATE est
 *           protégé par l'id, le nom et la valeur "non", et peut être rejoué sans risque.
 * --apply : corrige la base locale via Eloquent (historique des révisions conservé).
 */
class FixNovelizations extends Command
{
    protected $signature = 'bdfi:fix-novelizations
        { file : Fichier source, relatif à storage/app/import/ (ex. le fichier COL complet) }
        { --sql : Écrit le script SQL de correction <nom>.novelizations.sql }
        { --apply : Corrige la base locale }
        { --encoding=auto : Encodage du fichier source : auto, utf8, cp437, cp1252... }';

    protected $description = 'Repère (et corrige) les titres de novellisation enregistrés à "non" en base';

    public function handle(BookImportPreparer $preparer): int
    {
        try {
            $prepared = $preparer->prepare($this->argument('file'), ['encoding' => $this->option('encoding')]);
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info(count($prepared->books) . " livre(s) lus dans {$prepared->sourcePath}");

        [$found, $notFound] = $this->search($prepared->books);

        $toFix = array_filter($found, fn ($f) => $f['current'] === IsNovelization::NON->value && !$f['conflict']);
        $conflicts = array_filter($found, fn ($f) => $f['conflict']);
        $different = array_filter($found, fn ($f) => !$f['conflict'] && $f['current'] !== IsNovelization::NON->value && $f['current'] !== $f['expected']);
        $ok = count($found) - count($toFix) - count($conflicts) - count($different);

        $this->newLine();
        $this->table(
            ['Textes novellisés (fichier)', 'À corriger', 'Déjà corrects', 'Autre valeur en base', 'Incohérents dans le fichier', 'Non trouvés'],
            [[count($found) + count($notFound), count($toFix), $ok, count($different), count($conflicts), count($notFound)]],
        );

        $this->renderList('À corriger', $toFix, fn ($f) => "id {$f['id']} {$f['name']} ({$f['authors']}) : non => {$f['expected']}");
        $this->renderList('Autre valeur en base (non modifiés)', $different, fn ($f) => "id {$f['id']} {$f['name']} : base {$f['current']}, fichier {$f['expected']}");
        $this->renderList('Incohérents dans le fichier (non modifiés)', $conflicts, fn ($f) => "id {$f['id']} {$f['name']} : " . implode(' / ', $f['values']));
        $this->renderList('Non trouvés en base (à vérifier à la main)', $notFound, fn ($n) => "{$n['name']} ({$n['authors']}) : {$n['reason']} — livre #{$n['book']}");

        $base = dirname($prepared->sourcePath) . DIRECTORY_SEPARATOR . pathinfo($prepared->sourcePath, PATHINFO_FILENAME);
        $this->writeCsv("{$base}.novelizations.csv", $found, $notFound);

        if ($this->option('sql')) {
            $this->writeSql("{$base}.novelizations.sql", $toFix);
        }

        if ($this->option('apply')) {
            $this->apply($toFix);
        } elseif ($toFix) {
            $this->line('<fg=gray>Base locale non modifiée : --apply pour la corriger'
                . ($this->option('sql') ? '.' : ', --sql pour générer le script de production.') . '</>');
        }

        return self::SUCCESS;
    }

    /**
     * @return array{0: array<int, array>, 1: array<int, array>} trouvés (par id de titre), non trouvés
     */
    private function search(array $books): array
    {
        $lookup = new BookLookup();
        $found = [];
        $notFound = [];
        $authorCache = [];

        foreach ($books as $index => $book) {
            foreach (BookLookup::texts($book) as $text) {
                $expected = (string) ($text['novellisation'] ?? '');
                if ($expected === '' || $expected === IsNovelization::NON->value) {
                    continue;
                }

                $name = BookLookup::norm($text['titre'] ?? '');
                $authors = BookLookup::authors($text['auteurs'] ?? []);
                $label = implode(', ', array_column($authors, 'label'));

                $ids = [];
                foreach ($authors as $a) {
                    $authorCache[$a['key']] ??= $lookup->findAuthor($a)?->id ?? false;
                    if ($authorCache[$a['key']] === false) {
                        $notFound[] = ['book' => $index + 1, 'name' => $name, 'authors' => $label, 'reason' => "auteur introuvable : {$a['label']}"];
                        continue 2;
                    }
                    $ids[] = $authorCache[$a['key']];
                }

                $title = $lookup->title($name, $ids, BookLookup::norm($text['titre_vo'] ?? ''), BookLookup::translators($text));

                if (!$title) {
                    $notFound[] = ['book' => $index + 1, 'name' => $name, 'authors' => $label,
                        'reason' => $ids ? 'titre introuvable (titre, titre VO, traducteurs ou auteurs différents)' : 'texte sans auteur'];
                    continue;
                }

                // Un même titre peut figurer dans plusieurs livres : une seule entrée, valeurs du fichier cumulées
                $entry = $found[$title->id] ?? [
                    'id' => $title->id, 'name' => $title->name, 'authors' => $label, 'book' => $index + 1,
                    'current' => $title->is_novelization?->value ?? (string) $title->getRawOriginal('is_novelization'),
                    'values' => [],
                ];
                $entry['values'][$expected] = $expected;
                $entry['expected'] = $expected;
                $entry['conflict'] = count($entry['values']) > 1;
                $found[$title->id] = $entry;
            }
        }

        return [$found, $notFound];
    }

    private function apply(array $toFix): void
    {
        $count = 0;

        DB::transaction(function () use ($toFix, &$count) {
            foreach ($toFix as $f) {
                $title = Title::find($f['id']);
                if ($title && $title->is_novelization === IsNovelization::NON) {
                    $title->is_novelization = $f['expected'];
                    $title->save();   // Revisionable : modification tracée
                    $count++;
                }
            }
        });

        $this->info("{$count} titre(s) corrigé(s) en base locale.");
    }

    private function writeSql(string $path, array $toFix): void
    {
        $pdo = DB::getPdo();
        $lines = [
            '-- Correction de titles.is_novelization (TitleSeeder avait tout forcé à "non").',
            '-- Généré par php artisan bdfi:fix-novelizations le ' . now()->format('Y-m-d H:i') . ' : ' . count($toFix) . ' titre(s).',
            '-- Chaque UPDATE ne modifie rien si le titre a été renommé ou déjà corrigé : rejouable sans risque.',
            '',
        ];

        foreach ($toFix as $f) {
            $lines[] = sprintf("UPDATE titles SET is_novelization = %s, updated_at = NOW() WHERE id = %d AND name = %s AND is_novelization = 'non';",
                $pdo->quote($f['expected']), $f['id'], $pdo->quote($f['name']));
        }

        file_put_contents($path, implode("\n", $lines) . "\n");
        $this->info('Script SQL (' . count($toFix) . " UPDATE) : {$path}");
    }

    private function writeCsv(string $path, array $found, array $notFound): void
    {
        $handle = fopen($path, 'w');
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, ['statut', 'id', 'titre', 'auteurs', 'base', 'fichier', 'livre', 'remarque'], ';');

        foreach ($found as $f) {
            $status = match (true) {
                $f['conflict'] => 'incohérent',
                $f['current'] === $f['expected'] => 'correct',
                $f['current'] === IsNovelization::NON->value => 'à corriger',
                default => 'autre valeur',
            };
            fputcsv($handle, [$status, $f['id'], $f['name'], $f['authors'], $f['current'], implode(' / ', $f['values']), $f['book'], ''], ';');
        }
        foreach ($notFound as $n) {
            fputcsv($handle, ['non trouvé', '', $n['name'], $n['authors'], '', '', $n['book'], $n['reason']], ';');
        }

        fclose($handle);
        $this->info("Rapport complet : {$path}");
    }

    private function renderList(string $title, array $items, callable $format): void
    {
        if (!$items) {
            return;
        }

        $limit = $this->getOutput()->isVerbose() ? PHP_INT_MAX : 20;

        $this->newLine();
        $this->info("{$title} (" . count($items) . ')');
        foreach (array_slice($items, 0, $limit) as $item) {
            $this->line('  - ' . Str::limit($format($item), 150));
        }
        if (count($items) > $limit) {
            $this->line('  ... et ' . (count($items) - $limit) . ' autre(s) (-v pour tout voir, ou le CSV)');
        }
    }
}
