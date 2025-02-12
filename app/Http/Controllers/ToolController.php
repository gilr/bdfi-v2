<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use DB;
use App\Models\Award;
use App\Models\AwardCategory;
use App\Enums\QualityStatus;

class ToolController extends Controller
{
    public function index()
    {
        return view('admin.outils.index');
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
            ORDER BY birth_date");

        return view('admin/outils/dates-bizarres', compact('auteurs', 'auteurs2'));
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
                ORDER BY date_death"));

        return view('admin/outils/manque-dates-naissance', compact('auteurs'));
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
                ORDER BY birth_date"));

        return view('admin/outils/manque-dates-deces', compact('auteurs'));
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
     * Respond to request get /reports/missing-countries
     *
     * @return \Illuminate\Http\Response
     */
    public function getMissingCountries()
    {
        /*
        Les auteurs de pays inconnus
        */

        $auteurs = $this->paginateArray(
            DB::select ("SELECT id, nom_bdfi, name, first_name, is_pseudonym, birth_date, date_death, slug FROM authors WHERE country_id=1 OR country_id IS NULL"));

        return view('admin/outils/manque-nationalite', compact('auteurs'));
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

        return view('admin/outils/etat-biographies', compact('auteurs', 'level'));
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

        return view('admin/outils/manque-prix', compact('date', 'results'));
    }

    /**
     * Respond to request get /tools/FbToday
     */
    public function getFbToday()
    {
        $today = date("-m-d");

        $auteurs = DB::select ("SELECT id, nom_bdfi, name, first_name, birth_date, date_death FROM authors WHERE 
            SUBSTR(birth_date,5,6) = '$today'
            ORDER BY birth_date");

        $auteurs2 = DB::select ("SELECT id, nom_bdfi, name, first_name, birth_date, date_death FROM authors WHERE 
            SUBSTR(date_death,5,6) = '$today'
            ORDER BY date_death");

        $today = date("d/m");
        $format = "%e %B";
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
            $format = preg_replace('#(?<!%)((?:%%)*)%e#', '\1%#d', $format);
        }
        setlocale (LC_TIME, "fr_FR.UTF8", "fr.UTF8", "fr_FR.UTF-8", "fr.UTF-8", "fr_FR", "french");
        $dateenclair = strftime ($format);

        return view('admin/outils/anniversaires-fb-jour', compact('today', 'dateenclair', 'auteurs', 'auteurs2'));
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
                ORDER BY birth_date");

            $data[$i]['auteurs2'] = DB::select ("SELECT id, nom_bdfi, name, first_name, birth_date, date_death FROM authors WHERE 
                SUBSTR(date_death,5,6) = '$day'
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
        return view('admin/outils/anniversaires-fb-semaine', compact('today', 'data'));
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
                ORDER BY birth_date");

            $data[$i]['auteurs2'] = DB::select ("SELECT id, nom_bdfi, name, first_name, birth_date, date_death FROM authors WHERE 
                SUBSTR(date_death,5,6) = '$day'
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
        return view('admin/outils/anniversaires-fb-mois', compact('today', 'data'));
    }

    /**
     * Responds to requests post & get /tools/ConvertContent
     *
     * @return \Illuminate\Http\Response
     */
    public function getConvertContent()
    {
        $data = array();
        return view('admin.outils.conversion-sommaire', compact('data'));
    }
    public function postConvertContent(Request $request)
    {
        $data = array();
        $data['content0'] = $request->input('content0');
        $data['format0'] = $request->input('format0');
        $data['format1'] = $request->input('format1');
        $data['content1'] = $this->convert($data['content0'], $data['format0'], $data['format1']);

        return view('admin/outils/conversion-sommaire', compact('data'));
    }
    private function convert($text, $from, $to)
    {
        $text = rtrim(ltrim($text));
        $liste = preg_split("/\r\n|\n|\r/", $text);
        $newliste = array();
        foreach ($liste as $record) {  //    "blablabla, NOM prénom" 9
        $record = rtrim(ltrim($record));
        if ($from == 'tvnp') {
            $pos = strrpos ($record, ',');
            $np = rtrim(ltrim(substr($record, $pos+1)));
            $titre = rtrim(ltrim(substr ($record, 0, $pos)));
            list($n, $p) = $this->np ($np);
        }
        else if ($from == 'tvdnp') {
            $pos = strrpos ($record, ', de');
            $np = rtrim(ltrim(substr($record, $pos+4)));
            $titre = rtrim(ltrim(substr ($record, 0, $pos)));
            list($n, $p) = $this->np ($np);
        }
        else if ($from == 'tvpn') {
            $pos = strrpos ($record, ',');
            $pn = rtrim(ltrim(substr($record, $pos+1)));
            $titre = rtrim(ltrim(substr ($record, 0, $pos)));
            list($p, $n) = $this->pn ($pn);
        }
        else if ($from == 'tvdpn') {
            $pos = strrpos ($record, ', de');
            $pn = rtrim(ltrim(substr($record, $pos+4)));
            $titre = rtrim(ltrim(substr ($record, 0, $pos)));
            list($p, $n) = $this->pn ($pn);
        }
        else if ($from == 'npvt') {
            list($np, $titre) = preg_split ('/,\s*/', $record, 2);
            $np = rtrim(ltrim($np));
            $titre = rtrim(ltrim($titre));
            list($n, $p) = $this->np ($np);
        }
            else { // ($from == 'pnvt')
                list($pn, $titre) = preg_split ('/,\s*/', $record, 2);
                $pn = rtrim(ltrim($pn));
                $titre = rtrim(ltrim($titre));
                list($p, $n) = $this->pn ($pn);
            }
            $newliste[] = $this->formate($titre, $n, $p, $to);
        }
        $result = implode ("\n", $newliste);
        return $result;
    }

    private function formate($titre, $n, $p, $to)
    {
        if ($to == 'tvnp') {
            return "$titre, $n $p";
        }
        else if ($to == 'tvpn') {
            return "$titre, $p $n";
        }
        else if ($to == 'npvt') {
            return "$n $p, $titre";
        }
        else if ($to == 'pnvt') {
            return "$p $n, $titre";
        }
        else if ($to == 'npt') {
            return strtoupper($n) . ";$p;$titre";
        }
        else { // if ($to == 'gil')
            return ": .o .     N     _" . strtoupper($n) . " $p                            $titre";
       }
    }

    private function np($np)
    {
        $arr = preg_split ('/\s+/', $np, 2);
        if (count($arr) == 1) {
            return array($arr[0], "");
        }
        return $arr;
    }
    private function pn($pn)
    {
        $arr = preg_split ('/\s+/', $pn, 2);
        if (count($arr) == 1) {
            //return list("", $arr[0]);
            return array("", $arr[0]);
        }
        return $arr;
    }

}
