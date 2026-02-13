<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Str;

class ConverterContent extends Component
{
    // pb UTF-8 sur auteur : OK - Str::padRight($auteur, 28)
    // pb des accents dans noms d'auteur : OK - Str::ascii
    // Gestion des noms de traducteurs multiples : ..
    // Noms traducteurs en majuscules
    // Gestion des noms d'auteurs multiples : ..

    public string $inputFormat = 'CSV-TAB';
    public string $outputFormat = 'COL';
    public string $inputData = '';
    public string $outputData = '';

    public function convert()
    {
        $text = $this->inputData;
        $from = $this->inputFormat;
        $to = $this->outputFormat;
        if ($this->inputFormat === "TEXT-TPN") {
            $this->convertFromTxt();
        }
        elseif ($this->inputFormat === "CSV-TAB") {
            $this->convertFromCsv();
        }
        else {
            // Non implémenté
            $this->outputData = "Format d'entrée non encore traité !";
        }
    }

    protected function getExpectedHeader(): array
    {
        return [
            'sexe', 'prénom', 'nom', 'titre', 'titre_original', "n° d'ordre", 'pagin.', 'titre_recueil', 't_o_recueil', 'genre', 'editeur', 'collection', 'nr', 'an_copy', 'o', 'an_paru', 'sextrad', 'traducteur'
        ];
    }

    protected function convertFromCsv()
    {
        $lines = explode("\n", trim($this->inputData));
        $headerLine = strtolower(array_shift($lines));
        $actualHeader = str_getcsv($headerLine, "\t");
        // Supprime les colonnes vides en fin d'en-tête
        while (end($actualHeader) === '') {
            array_pop($actualHeader);
        }

        $expectedHeader = $this->getExpectedHeader();
        $printExpetedHeader = implode(" ", $expectedHeader);

        if ($actualHeader !== $expectedHeader) {
            //            dd($actualHeader, $expectedHeader)
            throw new \Exception("En-tête CSV invalide : les colonnes reçues [ $headerLine ] ne correspondent pas à l'attendu [ $printExpetedHeader ]");
        }

        //dd($lines);
        $data = [];
        foreach ($lines as $line) {
            if (trim($line) === '') continue;

            $rowValues = str_getcsv($line, "\t");

            // Ajuste le nombre de colonnes à celui de l'en-tête
            $rowValues = array_slice($rowValues, 0, count($actualHeader)); // tronque si trop long
            $rowValues = array_pad($rowValues, count($actualHeader), '');  // complète si trop court

            $row = array_combine($actualHeader, $rowValues);
            $data[] = $this->map($row);
        }

        $entete = "Editeur " . $row['editeur'] . ", Collection " . $row['collection'] . ", n° " . $row['nr'] . ", année " . $row['an_paru']. "\n";

        $result = $data;
        $this->exportData = implode(PHP_EOL, $result);
        $this->outputData = $this->exportData;

        session([
            'exportData' => $this->exportData,
            'exportFormat' => strtolower($this->outputFormat),
        ]);

    }

    /**
     *   Fonction de mapping entre les données du CSV et les données voulues V1
    **/
    protected function map(array $csvRow): string
    {
            $col_est_genre = $this->getEstGenre($csvRow['genre']);
            $col_genre   = $this->getGenre($csvRow['genre']);
            $col_type    = $this->getType($csvRow['genre']);
            $prenom      = $csvRow['prénom'];
            $nom         = $csvRow['nom'];

            // TODO : gérer les auteurs multiples
            $auteur = ($nom === "" ?  "$prenom" : mb_strtoupper(Str::ascii($nom)) . " $prenom");
            // Spliter avec " + " ou " & "

            $titre = trim($csvRow['titre']);
            $page  = trim($csvRow['pagin.']);
            if (($deb = strpos($page, "-", 0)) !== false) {
                $page = substr($page, 0, $deb);
            }
            $page = ltrim($page);
            $page = ltrim($page, "0");

            $cycle = "";

            $copyright   = trim($csvRow['an_copy']);
            $titre_vo    = $this->getTitreVO(trim($csvRow['titre_original']));
            $traducteurs = $this->getTraducteurs(trim($csvRow['traducteur']));

            return sprintf(
                ": .%1s%1s.%4s %1s     _%-28s%s%s %1s%s%s%s",
                $col_est_genre,
                $col_genre,
                $page,
                $col_type,
                Str::padRight($auteur, 28),
                $titre,
                $cycle,
                "■",
                $copyright,
                $titre_vo,
                $traducteurs
            );

    }

    protected function getEstGenre(string $value): string
    {
        return match (trim(strtolower($value))) {
            'sf' => 'o',
            'fy' => 'o',
            'fant' => 'o',
            'hyb' => 'o',
            'hg' => 'x',
            'préface' => 'o',
            'postface' => 'o',
            'rédactionnel' => 'o',
            default => '?',
        };
    }
    protected function getGenre(string $value): string
    {
        return match (trim(strtolower($value))) {
            'sf' => 'S',
            'fy' => 'Y',
            'fant' => 'F',
            'hyb' => 'I',
            'hg' => ' ',
            'préface' => ' ',
            'postface' => ' ',
            'rédactionnel' => ' ',
            default => ' ',
        };
    }
    protected function getType(string $value): string
    {
        return match (trim(strtolower($value))) {
            'sf' => 'N',
            'fy' => 'N',
            'fant' => 'N',
            'hyb' => 'N',
            'hg' => 'N',
            'préface' => 'p',
            'postface' => 'o',
            'rédactionnel' => 'a',
            default => ' ',
        };
    }

    protected function getTraducteurs(string $value): string
    {

        if ($value === "_") {
            return "";
        }

        $traducteurs = StrTraducteursToCol($value);
        return " ■Trad. " . $traducteurs;
    }

    protected function getTitreVO(string $value): string
    {
        return ($value === "_" ? "" : " " . $value);
    }

    public function convertFromTxt()
    {
        $text = $this->inputData;

        $text = rtrim(ltrim($text));
        $liste = preg_split("/\r\n|\n|\r/", $text);
        $newliste = array();

        // si les lignes commencent toutes par '"' ou équivalent, alors les enlever
        $toutesCommencentParGuillemet = true;
        foreach ($liste as $ligne) {
            if (($ligne[0] !== '"') && (mb_substr($ligne, 0, 1, "UTF-8") !== '“') && ($ligne[0] !== '«') && ($ligne[0] !== '«')) {
                $toutesCommencentParGuillemet = false;
                break;
            }
        }

        if ($toutesCommencentParGuillemet) {
            $liste = array_map(function ($ligne) {
                // Retirer le premier guillemet
                $ligne = mb_substr($ligne, 1, mb_strlen($ligne, 'UTF-8'), "UTF-8");
                return $ligne;
            }, $liste);
        }

        // si les lignes finissent toutes par '"', alors enlever, et le dernier (après retrait) devient le "séparateur" -> remplacer par ####
        // + tard (pour ordre inverse, nom puis titre)

        // si toutes les lignes contiennent ", de " ou ", d'", c'est le séparateur, remplacer la dernière occurence par ####
        // TODO - pb avec les " d'"... why ??a - le motif n'est pas trouvé ?
        $patterns = [
            // Apostrophe + virgule + de
            "», de ",
            "\", de ",
            "”, de ",
            // Apostrophe + de
            "» de ",
            "\" de ",
            "” de ",
            // Apostrophe + virgule + d'
            "», d'",
            "\", d'",
            "”, d'",
            // Apostrophe + d'
            "» d'",
            "\" d'",
            "” d'",
            // virgule + de ou d'
            ", de ",
            ", d'",
            // cas "d'erreur"
            "»,de ",
            "\",de ",
            "”,de ",
            "»de ",
            "\"de ",
            "”de ",
            "»,d'",
            "\",d'",
            "”,d'",
            "»d'",
            "\"d'",
            "”d'",
        ];
        $separateur = '####';

        // Vérification : toutes les lignes contiennent au moins un des motifs
        $toutesContiennentUnPattern = true;
        foreach ($liste as $ligne) {
            $contient = false;
            foreach ($patterns as $pattern) {
                if (mb_strpos($ligne, $pattern, 0, 'UTF-8') !== false) {
                    $contient = true;
                    break;
                }
            }
            if (!$contient) {
                $toutesContiennentUnPattern = false;
                break;
            }
        }

        // DEBUG
        // $this->outputData = $toutesContiennentUnPattern ? "OK" : "NOK";
        // return;

        // Remplacement du dernier motif par le séparateur, uniquement si toutes les lignes sont conformes
        if ($toutesContiennentUnPattern) {
            $liste = array_map(function ($ligne) use ($patterns, $separateur) {
                $dernierPos = -1;
                $dernierPattern = '';

                foreach ($patterns as $pattern) {
                    $pos = mb_strrpos($ligne, $pattern, 0, "UTF-8");
                    if ($pos !== false && $pos > $dernierPos) {
                        $dernierPos = $pos;
                        $dernierPattern = $pattern;
                    }
                }

                if ($dernierPos !== -1) {
                    $ligne = $this->mb_substr_replace($ligne, $separateur, $dernierPos, mb_strlen($dernierPattern));
                }

                return $ligne;
            }, $liste);
        }
        else
        {
            // Pattern "classiques" non trouvé
            // Vérification : toutes les lignes contiennent au moins " de "
            $pattern = ", ";
            $toutesContiennentUnPattern = true;
            foreach ($liste as $ligne) {
                $contient = false;
                if (mb_strpos($ligne, $pattern, 0, 'UTF-8') === false) {
                    $toutesContiennentUnPattern = false;
                    break;
                }
            }

            //dd ($toutesContiennentUnPattern);
            // Remplacement du dernier motif par le séparateur, uniquement si toutes les lignes sont conformes
            if ($toutesContiennentUnPattern) {
                $liste = array_map(function ($ligne) use ($pattern, $separateur) {

                    $pos = mb_strrpos($ligne, $pattern, 0, "UTF-8");

                    if ($pos !== -1) {
                        $ligne = $this->mb_substr_replace($ligne, $separateur, $pos, mb_strlen($pattern));
                    }

                    return $ligne;
                }, $liste);
            }
        }

        // DEBUG
        // $this->outputData = implode ("\n", $liste);
        // return;

        // si toutes les lignes contiennent " de ", c'est le séparateur, remplacer la dernière occurence par #X#
        // si toutes les lignes contiennent ", ", c'est le séparateur, remplacer la dernière occurence par #X#
        // a voir

        foreach ($liste as $record) {  //    "blablabla, NOM prénom" 9
            $record = rtrim(ltrim($record));
            if ($from == 'TEXT-TNP') {
                $pos = mb_strrpos ($record, '####', 0, "UTF-8");
                $np = rtrim(ltrim(mb_substr($record, $pos+4, 99, 'UTF-8')));
//                $pos = strrpos ($record, ',');
//                $np = rtrim(ltrim(substr($record, $pos+1)));
                $titre = rtrim(ltrim(mb_substr ($record, 0, $pos, 'UTF-8')));
                list($n, $p) = $this->np ($np);
            }
            else if ($from == 'TEXT-TPN') {
                $pos = mb_strrpos ($record, '####', 0, "UTF-8");
                $pn = rtrim(ltrim(mb_substr($record, $pos+4, 99, 'UTF-8')));
//                $pos = strrpos ($record, ',');
//                $pn = rtrim(ltrim(substr($record, $pos+1)));
                $titre = rtrim(ltrim(mb_substr ($record, 0, $pos, 'UTF-8')));
                list($p, $n) = $this->pn ($pn);
            }

            //
            // Suite non encore finalisée / testée
            //
            else if ($from == 'TEXT-NPT') {
                list($np, $titre) = preg_split ('/,\s*/', $record, 2);
                $np = rtrim(ltrim($np));
                $titre = rtrim(ltrim($titre));
                list($n, $p) = $this->np ($np);
            }
            else { // ($from == 'TEXT-PNT') {
                list($pn, $titre) = preg_split ('/,\s*/', $record, 2);
                $pn = rtrim(ltrim($pn));
                $titre = rtrim(ltrim($titre));
                list($p, $n) = $this->pn ($pn);
            }

            $auteur = strtoupper($n) . " $p";
            $auteurFormate = $this->padCp437($auteur, 28);
            $newliste[] = sprintf(
                ": .o .     N     _%s%s %1s%s",
                $auteurFormate,
                iconv('UTF-8', 'CP437//TRANSLIT', $titre, ),
                iconv('UTF-8', 'CP437//TRANSLIT', "■"),
                "2025"
            );
        }
        $this->exportData = implode(PHP_EOL, $newliste);

        $this->outputData = match ($this->outputFormat) {
            'COL'   => iconv('CP437', 'UTF-8', $this->exportData),
            default => iconv('CP437', 'UTF-8', $this->exportData),
        };

        session([
            'exportData' => $this->exportData,
            'exportFormat' => strtolower($this->outputFormat),
        ]);

    }

    public function render()
    {
        return view('livewire.converter-content');
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

    private function mb_substr_replace($string, $replacement, $start, $length = null, $encoding = 'UTF-8') {
        $before = mb_substr($string, 0, $start, $encoding);
        $after = $length === null
            ? ''
            : mb_substr($string, $start + $length, null, $encoding);

        return $before . $replacement . $after;
    }


    /**
     * Pad a string to a fixed width after converting to CP437.
     *
     * @param string $text   Texte en UTF-8
     * @param int    $width  Largeur cible en caractères CP437
     * @return string        Texte converti et padé
     **/
    private function padCp437(string $text, int $width): string
    {
        // Conversion avec translit pour éviter les erreurs
        $converted = iconv('UTF-8', 'CP437//TRANSLIT', $text);

        // Longueur réelle en octets CP437
        $length = strlen($converted);

        // Troncature ou padding
        if ($length >= $width) {
            return substr($converted, 0, $width);
        }

        return $converted . str_repeat(' ', $width - $length);
    }

}
