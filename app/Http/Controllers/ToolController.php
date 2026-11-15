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
        return view('admin.outils.index');   // zone Outils (ex-Labs)
    }
    public function labIndex()
    {
        return view('admin.labs.index');   // zone Labs (essais divers)
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

        return view('admin.outils.conversion-sommaire', compact('data'));
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
