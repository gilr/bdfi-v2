<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use DB;
use App\Models\Award;
use App\Models\AwardCategory;
use App\Enums\QualityStatus;

class ReportController extends Controller
{

    public function index()
    {
        return view('admin.rapports.index');
    }

    /**
     * Respond to request get /reports/missing-records
     *
     */
    public function getMissingRecords()
    {

        $auteurs_db = Auteur::orderBy('nom_bdfi')->pluck('nom_bdfi')->toArray();

        $file = "../../data/auteurs.res";
//      $file = "./auteurs.res";

        // $auteurs_file = file($file, FILE_IGNORE_NEW_LINES);
        // ...marcherait presque... mais pb CP850 <-> utf8

        $auteurs_file = array();
        $nb = 0;
        $nbok = 0;
        if ($fd = fopen ($file, 'r'))
        {
            while (!feof ($fd))
            {
                $ligne = fgets ($fd, 4096);
                $nb = $nb + 1;
                // Au delà de 4 pour ne pas prendre en compte les ***, ---inconnu et ---anonyme
                if (($nb >= 4) && (strlen($ligne) != 0)) {
                    $ligne = chop($ligne);
                    $ligne=iconv("CP850", "UTF-8", $ligne);
                    $ligne = preg_replace('/Ø([A-Z])/', 'O${1}', $ligne);   
                    $auteurs_file[] = $ligne;
                }
            }
            fclose($fd);
        }
        $missingall = array_diff($auteurs_file, $auteurs_db);
        $nombre = count($missingall);
        $missing = array_slice($missingall, 0, 500);

        return view('admin/rapports/manque_fiches', compact('nombre', 'missing'));
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

        $auteurs = DB::select ("SELECT id, nom_bdfi, name, first_name, birth_date, date_death FROM authors WHERE
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
        $auteurs2 = DB::select ("SELECT id, nom_bdfi, name, first_name, birth_date, date_death FROM authors WHERE
            SUBSTR(date_death,1,4) < '0000' OR
            SUBSTR(date_death,1,4) > '$year' OR
            SUBSTR(date_death,5,1) <> '-' OR
            SUBSTR(date_death,6,2) < '00' OR
            SUBSTR(date_death,6,2) > '12' OR
            SUBSTR(date_death,8,1) <> '-' OR
            SUBSTR(date_death,9,2) < '00' OR
            SUBSTR(date_death,9,2) > '31' 
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
            DB::select ("SELECT id, nom_bdfi, name, first_name, birth_date, date_death FROM authors WHERE 
                (SUBSTR(birth_date,1,4)='0000' OR birth_date IS NULL) AND 
                SUBSTR(date_death,1,4)<>'0000'
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
            DB::select ("SELECT id, nom_bdfi, name, first_name, birth_date, date_death FROM authors WHERE 
                SUBSTR(date_death,1,4)='0000' AND 
                SUBSTR(birth_date,1,4)<>'0000' AND 
                CAST(SUBSTR(birth_date,1,4) AS UNSIGNED) < 1925 
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
            DB::select ("SELECT id, nom_bdfi, name, first_name, is_pseudonym, birth_date, date_death FROM authors WHERE country_id=1 OR country_id IS NULL"));

        return view('admin/rapports/manque-nationalite', compact('auteurs'));
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
        if ($level == 0) { $state = QualityStatus::VIDE->value; }
        if ($level == 1) { $state = QualityStatus::EBAUCHE->value; }
        if ($level == 2) { $state = QualityStatus::MOYEN->value; }
        if ($level == 3) { $state = QualityStatus::ACCEPTABLE->value; }
        if ($level == 4) { $state = QualityStatus::TERMINE->value; }
        if ($level == 5) { $state = QualityStatus::VALIDE->value; }
        if ($level == 9) { $state = QualityStatus::A_REVOIR->value; }

        $auteurs = $this->paginateArray(
            DB::select ("SELECT id, nom_bdfi, name, first_name, birth_date, date_death FROM authors WHERE quality='$state'"));

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
}
