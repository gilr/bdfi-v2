<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use DB;
use App\Models\Award;
use App\Models\AwardCategory;
use App\Models\Title;
use App\Models\Event;
use App\Enums\EventPeriodicity;
use App\Enums\TitleVariantType;
use App\Bdfi\VariantDates;
use App\Enums\QualityStatus;

class ReportController extends Controller
{

    public function index()
    {
        return view('admin.rapports.index');
    }

    /**
     * Respond to request get /reports/strange-dates
     *
     * @return \Illuminate\Http\Response
     */
    public function getStrangeDates()
    {
        /*
         ...
         ... manque les dates valides syntaxiquement mais qui n'existent pas (callback sur checkdate php)
        */
        $year = date("Y") - 19;

        $auteurs = DB::select ("SELECT id, nom_bdfi, name, first_name, birth_date, date_death, slug FROM authors WHERE
            SUBSTR(birth_date,1,4) < '0000' OR
            SUBSTR(birth_date,1,4) > '$year' OR
            SUBSTR(birth_date,5,1) <> '-' OR
            SUBSTR(birth_date,6,2) < '00' OR
            SUBSTR(birth_date,6,2) > '12' OR
            SUBSTR(birth_date,8,1) <> '-' OR
            SUBSTR(birth_date,9,2) < '00' OR
            SUBSTR(birth_date,9,2) > '31'
            AND deleted_at IS NULL
            ORDER BY birth_date");

        $year = date("Y");
        $auteurs2 = DB::select ("SELECT id, nom_bdfi, name, first_name, birth_date, date_death, slug FROM authors WHERE
            SUBSTR(date_death,1,4) < '0000' OR
            SUBSTR(date_death,1,4) > '$year' OR
            SUBSTR(date_death,5,1) <> '-' OR
            SUBSTR(date_death,6,2) < '00' OR
            SUBSTR(date_death,6,2) > '12' OR
            SUBSTR(date_death,8,1) <> '-' OR
            SUBSTR(date_death,9,2) < '00' OR
            SUBSTR(date_death,9,2) > '31'
            AND deleted_at IS NULL
            ORDER BY birth_date");

        return view('admin/rapports/dates-bizarres', compact('auteurs', 'auteurs2'));
     }

    /**
     * Respond to request get /reports/missing-birthdates
     *
     * @return \Illuminate\Http\Response
     */
    public function getMissingBirthdates()
    {
        //echo "Route getMissingBirthdate1(), depuis /reports/missing-birthdates";
        /*
        Les auteurs d'année de naissance inconnue
        ... alors que l'année de décès est connue...
        */

        $auteurs = $this->paginateArray(
            DB::select ("SELECT id, nom_bdfi, name, first_name, birth_date, date_death, slug FROM authors WHERE
                (SUBSTR(birth_date,1,4)='0000' OR birth_date IS NULL) AND
                SUBSTR(date_death,1,4)<>'0000'
                AND deleted_at IS NULL
                ORDER BY date_death"));

        return view('admin/rapports/manque-dates-naissance', compact('auteurs'));
    }

    /**
     * Respond to request get /reports/missing-deathdates
     *
     * @return \Illuminate\Http\Response
     */
    public function getMissingDeathdates()
    {
        /*
        Les auteurs d'année de décès inconnue
        ... et dont l'année de naissance est connue, et de plus de 90 ans (ce qui leur donne le droit d'être encore en vie :) ...
        */
        $auteurs = $this->paginateArray(
            DB::select ("SELECT id, nom_bdfi, name, first_name, birth_date, date_death, slug FROM authors WHERE
                SUBSTR(date_death,1,4)='0000' AND
                SUBSTR(birth_date,1,4)<>'0000' AND
                CAST(SUBSTR(birth_date,1,4) AS UNSIGNED) < 1925
                AND deleted_at IS NULL
                ORDER BY birth_date"));

        return view('admin/rapports/manque-dates-deces', compact('auteurs'));
    }

    public function paginateArray($data, $perPage = 15)
    {
        $page = Paginator::resolveCurrentPage();
        $total = count($data);
        $results = array_slice($data, ($page - 1) * $perPage, $perPage);

        return new LengthAwarePaginator($results, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
        ]);
    }

    /**
     * Respond to request get /admin/rapports/manque-nationalite
     *
     * @return \Illuminate\Http\Response
     */
    public function getMissingCountries()
    {
        /*
        Les auteurs de pays inconnus
        */

        $auteurs = $this->paginateArray(
            DB::select ("
                SELECT id, nom_bdfi, name, first_name, is_pseudonym, birth_date, date_death, slug 
                FROM authors 
                WHERE (country_id=1 OR country_id IS NULL)
                AND deleted_at IS NULL
                "));

        return view('admin/rapports/manque-nationalite', compact('auteurs'));
    }

    /**
     * Respond to request get /admin/rapports/erreur-nationalite
     *
     * @return \Illuminate\Http\Response
     */
    public function getCountryErrors()
    {
        /*
        Les auteurs avec nation en double, ou pays2 mais pas pays principal
        */

        $auteurs = $this->paginateArray(
            DB::select("
                SELECT id, nom_bdfi, name, first_name, is_pseudonym, birth_date, date_death, slug, country_id, country2_id
                FROM authors
                WHERE country2_id <> 1
                AND deleted_at IS NULL
                AND country2_id IS NOT NULL
                AND (country_id = country2_id OR country_id = 1 OR country_id IS NULL)
            "));

        return view('admin/rapports/erreur-nationalite', compact('auteurs'));
    }

    /**
     * Respond to request get /reports/bio-status-0
     *
     * @return \Illuminate\Http\Response
     */
    public function getBioStatus($level)
    {
        /*
        Les bios d'un status donné
        */

        if ($level == 1) { $state = QualityStatus::EBAUCHE->value; }
        elseif ($level == 2) { $state = QualityStatus::MOYEN->value; }
        elseif ($level == 3) { $state = QualityStatus::ACCEPTABLE->value; }
        elseif ($level == 4) { $state = QualityStatus::TERMINE->value; }
        elseif ($level == 5) { $state = QualityStatus::VALIDE->value; }
        elseif ($level == 9) { $state = QualityStatus::A_REVOIR->value; }
        else {
            // Valeur par défaut ou si ($level == 0)
            $state = QualityStatus::VIDE->value;
        }

        $auteurs = $this->paginateArray(
            DB::select ("SELECT id, nom_bdfi, name, first_name, birth_date, date_death, slug FROM authors WHERE quality='$state'"));

        return view('admin/rapports/etat-biographies', compact('auteurs', 'level'));
    }

    public function getMissingAwards($date)
    {
        /*
        Prix manquants... ou abandonnés
        */
        $active_awards = Award::where('year_end', '=' ,'')->get();
        $results = collect();
        foreach ($active_awards as $award) {
            $id = $award->id;
            $max = (array)DB::selectOne("SELECT MAX(year) FROM award_winners w, award_categories c WHERE w.award_category_id = c.id AND c.award_id = $id");
            $lastyear = $max[key($max)];
            if($lastyear <= $date) {
                $categories = AwardCategory::where('award_id', '=' ,"$id")->get();
                $results->push([$award, $lastyear, $categories]);
            }
        }

        return view('admin/rapports/manque-prix', compact('date', 'results'));
    }

    /**
     * Respond to request get /admin/rapports/erreurs-variantes
     * Textes dont le rattachement parent/variantes est incohérent (un seul parent, "premier" ou "virtuel",
     * toutes les variantes rattachées directement à lui).
     */
    public function getVariantErrors()
    {
        $valid = array_map(fn ($c) => $c->value, TitleVariantType::cases());
        $parentTypes = [TitleVariantType::PREMIER->value, TitleVariantType::VIRTUEL->value];
        $in = fn (array $values) => "'" . implode("','", $values) . "'";

        // Par ordre de gravité décroissante : ce même ordre sert à classer les lignes du rapport
        $queries = [
            'Type de variante inconnu' => "SELECT id FROM titles WHERE deleted_at IS NULL AND variant_type NOT IN ({$in($valid)})",
            'Parent supprimé' => "SELECT c.id FROM titles c JOIN titles p ON p.id = c.parent_id
                WHERE c.deleted_at IS NULL AND p.deleted_at IS NOT NULL",
            'Parent de lui-même' => "SELECT id FROM titles WHERE deleted_at IS NULL AND parent_id = id",
            'Parent et enfant' => "SELECT t.id FROM titles t WHERE t.deleted_at IS NULL AND t.parent_id <> t.id
                AND t.variant_type IN ({$in($valid)}) AND t.variant_type NOT IN ({$in($parentTypes)})
                AND EXISTS (SELECT 1 FROM titles k WHERE k.parent_id = t.id AND k.id <> t.id AND k.deleted_at IS NULL)",
            'Premier ou virtuel, et enfant' => "SELECT id FROM titles WHERE deleted_at IS NULL AND parent_id IS NOT NULL
                AND parent_id <> id AND variant_type IN ({$in($parentTypes)})",
            'Parent de type variante' => "SELECT t.id FROM titles t WHERE t.deleted_at IS NULL AND t.parent_id IS NULL
                AND t.variant_type IN ({$in($valid)}) AND t.variant_type NOT IN ({$in($parentTypes)})
                AND EXISTS (SELECT 1 FROM titles k WHERE k.parent_id = t.id AND k.deleted_at IS NULL)",
            'Variante sans parent' => "SELECT t.id FROM titles t WHERE t.deleted_at IS NULL AND t.parent_id IS NULL
                AND t.variant_type IN ({$in($valid)}) AND t.variant_type NOT IN ({$in($parentTypes)})
                AND NOT EXISTS (SELECT 1 FROM titles k WHERE k.parent_id = t.id AND k.deleted_at IS NULL)",
        ];

        // id du texte => libellés des erreurs, et rang de sa première erreur (la plus grave)
        $errors = [];
        $rank = [];
        $position = 0;
        foreach ($queries as $label => $sql) {
            foreach (DB::select($sql) as $row) {
                $errors[$row->id][] = $label;
                $rank[$row->id] ??= $position;
            }
            $position++;
        }

        // Variante parue avant son parent (dates connues des deux) : présélection par l'année en SQL,
        // puis comparaison précise (mois, trimestre) en PHP. Un épisode de feuilleton est normalement
        // antérieur au texte complet : ce n'est pas une erreur.
        $pairs = DB::select("SELECT c.id, c.copyright_fr AS child_date, p.copyright_fr AS parent_date
            FROM titles c JOIN titles p ON p.id = c.parent_id
            WHERE c.deleted_at IS NULL AND p.deleted_at IS NULL AND c.parent_id <> c.id
            AND c.variant_type <> 'feuilleton'
            AND SUBSTR(c.copyright_fr, 1, 4) <= SUBSTR(p.copyright_fr, 1, 4)"); // SUBSTR (et non LEFT) : compatible MySQL et SQLite (tests)
        foreach ($pairs as $pair) {
            if (VariantDates::isEarlier($pair->child_date, $pair->parent_date)) {
                $errors[$pair->id][] = 'Date fr antérieure à celle du parent (' . $pair->parent_date . ')';
                $rank[$pair->id] ??= $position;
            }
        }

        $titles = Title::with('authors')->whereIn('id', array_keys($errors))->get()
            ->sort(fn ($a, $b) => [$rank[$a->id], $a->name] <=> [$rank[$b->id], $b->name])
            ->values();

        return view('admin/rapports/erreurs-variantes', compact('titles', 'errors'));
    }

    /**
     * Respond to request get /admin/rapports/evenements-a-completer
     * Évènements toujours actifs (sans année de fin) dont la dernière occurrence est terminée depuis plus d'un
     * certain délai, à peu près la moitié de l'intervalle entre deux occurrences (5 mois pour un annuel) :
     * la suivante est peut-être annoncée, ou l'évènement a cessé.
     * Une périodicité inconnue ou irrégulière est traitée comme annuelle ; un évènement unique terminé
     * n'apparaît que pour être déclaré clos.
     */
    public function getMissingEventOccurrences()
    {
        $delays = [
            EventPeriodicity::BIENNAL->value => 11,
            EventPeriodicity::UNIQUE->value => 0,
        ];
        $defaultDelay = 5;

        $events = Event::with(['occurrences' => fn ($q) => $q->orderByDesc('start_date')])
            ->where(fn ($q) => $q->whereNull('year_end')->orWhere('year_end', ''))
            ->get();

        $rows = collect();
        foreach ($events as $event) {
            $last = $event->occurrences->first();
            $months = $delays[$event->periodicity?->value] ?? $defaultDelay;
            if ($last && $last->end_date->copy()->addMonths($months)->isFuture()) {
                continue;
            }
            $rows->push((object) ['event' => $event, 'last' => $last]);
        }

        // Sans occurrence en tête, puis de la plus récente (sans doute à ajouter) à la plus ancienne (sans doute close)
        $rows = $rows->sortByDesc(fn ($r) => $r->last?->end_date->format('Y-m-d') ?? '9999')->values();

        return view('admin/rapports/evenements-a-completer', compact('rows', 'defaultDelay'));
    }

    /**
     * Respond to request post /admin/rapports/evenements-a-completer/clore
     * Déclare l'évènement clos : année de fin = année de sa dernière occurrence.
     */
    public function closeEvent(Request $request)
    {
        abort_unless(auth()->user()?->hasMemberRole(), 403);

        $event = Event::with('occurrences')->findOrFail((int) $request->input('event'));
        $last = $event->occurrences->sortByDesc('start_date')->first();
        if (!$last) {
            return back()->with('error', "« {$event->name} » n'a aucune occurrence : année de fin à saisir dans sa fiche.");
        }

        $event->year_end = $last->start_date->format('Y');
        $event->save();

        return back()->with('status', "« {$event->name} » déclaré clos en {$event->year_end}.");
    }

    /**
     * Respond to request get /tools/FbToday
     */
    public function getFbToday()
    {
        $today = date("-m-d");

        $auteurs = DB::select ("SELECT id, nom_bdfi, name, first_name, birth_date, date_death FROM authors WHERE 
            SUBSTR(birth_date,5,6) = '$today'
            AND deleted_at IS NULL
            ORDER BY birth_date");

        $auteurs2 = DB::select ("SELECT id, nom_bdfi, name, first_name, birth_date, date_death FROM authors WHERE 
            SUBSTR(date_death,5,6) = '$today'
            AND deleted_at IS NULL
            ORDER BY date_death");

        $today = date("d/m");
        $format = "%e %B";
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
            $format = preg_replace('#(?<!%)((?:%%)*)%e#', '\1%#d', $format);
        }
        setlocale (LC_TIME, "fr_FR.UTF8", "fr.UTF8", "fr_FR.UTF-8", "fr.UTF-8", "fr_FR", "french");
        $dateenclair = strftime ($format);

        return view('admin/rapports/anniversaires-fb-jour', compact('today', 'dateenclair', 'auteurs', 'auteurs2'));
    }

    /**
     * Respond to request get /tools/FbWeek
     *
     * @return \Illuminate\Http\Response
     */
    public function getFbWeek()
    {
        $data = array();
        for ($i=1; $i<8; $i++) {
            $day = date("-m-d", time() + $i * 24 * 60 * 60);
            $data[$i] = array();
            
            $data[$i]['day'] = $day;
            $data[$i]['auteurs'] = DB::select ("SELECT id, nom_bdfi, name, first_name, birth_date, date_death FROM authors WHERE 
                SUBSTR(birth_date,5,6) = '$day'
                AND deleted_at IS NULL
                ORDER BY birth_date");

            $data[$i]['auteurs2'] = DB::select ("SELECT id, nom_bdfi, name, first_name, birth_date, date_death FROM authors WHERE 
                SUBSTR(date_death,5,6) = '$day'
                AND deleted_at IS NULL
                ORDER BY date_death");

            $day = date("d/m", time() + $i * 24 * 60 * 60);
            $data[$i]['day'] = $day;
            $format = "%e %B";
            if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
                $format = preg_replace('#(?<!%)((?:%%)*)%e#', '\1%#d', $format);
            }
            setlocale (LC_TIME, "fr_FR.UTF8", "fr.UTF8", "fr_FR.UTF-8", "fr.UTF-8", "fr_FR", "french");
            $data[$i]['dateenclair'] = strftime ($format, time() + $i * 24 * 60 * 60);
        }
        $today = date("d/m");
        return view('admin/rapports/anniversaires-fb-semaine', compact('today', 'data'));
    }

    /**
     * Respond to request get /tools/FbMonth
     *
     * @return \Illuminate\Http\Response
     */
    public function getFbMonth()
    {
        $data = array();
        for ($i=1; $i<28; $i++) {
            $day = date("-m-d", time() + $i * 24 * 60 * 60);
            $data[$i] = array();
            
            $data[$i]['day'] = $day;
            $data[$i]['auteurs'] = DB::select ("SELECT id, nom_bdfi, name, first_name, birth_date, date_death FROM authors WHERE 
                SUBSTR(birth_date,5,6) = '$day'
                AND deleted_at IS NULL
                ORDER BY birth_date");

            $data[$i]['auteurs2'] = DB::select ("SELECT id, nom_bdfi, name, first_name, birth_date, date_death FROM authors WHERE 
                SUBSTR(date_death,5,6) = '$day'
                AND deleted_at IS NULL
                ORDER BY date_death");

            $day = date("d/m", time() + $i * 24 * 60 * 60);
            $data[$i]['day'] = $day;
            $format = "%e %B";
            if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
                $format = preg_replace('#(?<!%)((?:%%)*)%e#', '\1%#d', $format);
            }
            setlocale (LC_TIME, "fr_FR.UTF8", "fr.UTF8", "fr_FR.UTF-8", "fr.UTF-8", "fr_FR", "french");
            $data[$i]['dateenclair'] = strftime ($format, time() + $i * 24 * 60 * 60);
        }
        $today = date("d/m");
        return view('admin/rapports/anniversaires-fb-mois', compact('today', 'data'));
    }
}
