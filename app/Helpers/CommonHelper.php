<?php


function InitialeCouv($file): string
{
    $initiale = substr($file, 0, 1);
    if (($initiale >= '0') && ($initiale <= '9'))
    {
        return '09';
    }
    return $initiale;
}


/**
 * Conversion d'une date DL ou AI type CM au format BDFI (aaaa-mm-jj)
 *
 * @return string
 */

function StrDLAItoBDFI($date, $approx_publi): string
{
    $date = strtolower(trim($date));

    if (strlen($date) < 1) {
        return $date;
    }

    $specialCases = [
        'a parution' => 'A parution',
        'n.i.' => 'Non indiq.'
    ];

    if (array_key_exists($date, $specialCases)) {
        return $specialCases[$date];
    }
    $dateParts = preg_split('/[\/\s-]/', $date);

    # Si déjà au bon format
    if ((count($dateParts) === 3) && (strlen($dateParts[0]) === 4) && (strlen($dateParts[1]) === 2) && (strlen($dateParts[2]) === 2))
    {
        return $date;
    }

    $year_approx=substr($approx_publi, 0, 4);

    $monthMappings = [
        'jan' => '01', 'janv' => '01',  'janv.' => '01',
        'fev' => '02', 'fevr' => '02', 'fevr.' => '02', 'fév' => '02', 'févr' => '02', 'févr.' => '02', 'feb' => '02',
        'mar' => '03', 'mars' => '03', 'march' => '03',
        'avr' => '04', 'avr.' => '04', 'apr' => '04',
        'mai' => '05', 'may' => '05',
        'jun' => '06', 'juin' => '06',
        'jul' => '07', 'juil' => '07', 'juil.' => '07',
        'aug' => '08', 'aou' => '08', 'aout' => '08', 'août' => '08',
        'sep' => '09', 'sept' => '09', 'sept.' => '09',
        'oct' => '10', 'oct.' => '10',
        'nov' => '11', 'nov.' => '11',
        'dec' => '12', 'dec.' => '12', 'déc' => '12', 'déc.' => '12',
        't1' => 'T1', '1t' => 'T1',
        't2' => 'T2', '2t' => 'T2',
        't3' => 'T3', '3t' => 'T3',
        't4' => 'T4', '4t' => 'T4'
    ];

    if (count($dateParts) === 1)
    {
        $year = (strlen($dateParts[0]) === 4) ? $dateParts[0] : StrDetectCentury($dateParts[0], $year_approx) . $dateParts[0];
        $month = '00';
        $day = '00';

        return "$year-$month-$day";
    }
    else if (count($dateParts) === 2)
    {
        $year = (strlen($dateParts[1]) === 4) ? $dateParts[1] : StrDetectCentury($dateParts[1], $year_approx) . $dateParts[1];
        $month = (isset($dateParts[0]) && array_key_exists($dateParts[0], $monthMappings)) ?
            $monthMappings[$dateParts[0]] : $dateParts[0];
        $day = '00';

        return "$year-$month-$day";
    }
    else if (count($dateParts) === 3)
    {
        $year = (strlen($dateParts[2]) === 4) ? $dateParts[2] : StrDetectCentury($dateParts[2], $year_approx) . $dateParts[2];
        $month = (isset($dateParts[1]) && array_key_exists($dateParts[1], $monthMappings)) ?
            $monthMappings[$dateParts[1]] : $dateParts[1];
        $day = (isset($dateParts[0]) && strlen($dateParts[0]) > 0) ? $dateParts[0] : '00';

        return "$year-$month-$day";
    }

    return "Err Decod.";
}

/**
 * Détection du siècle des dates DL / AI si seuls les deux derniers chiffres sont connus (ex.: "Oct 03")
 *
 * @return string
 */
function StrDetectCentury($aa, $publi): string
{
    if ($aa > 90) {
        if ($publi > 1990) { return '19'; }
        if ($publi > 1890) { return '18'; }
        if ($publi > 1790) { return '17'; }
        if ($publi > 1690) { return '16'; }
        else { return '15'; };
    }
    else if ($aa < 10) {
        if ($publi < 1610) { return '16'; }
        if ($publi < 1710) { return '17'; }
        if ($publi < 1810) { return '18'; }
        if ($publi < 1910) { return '19'; }
        else { return '20'; };
    }
    else {
        return substr($publi, 0, 2);
    }
}


/**
 * Conversion d'une date au format BDFI (aaaa-mm-jj) vers un format affichable clair  (12 avril 1986, 1er trimestre 2005...)
 *
 * @return string
 */
function StrDateformat ($date)
{
    $format = 'abr';
    $user = Auth::user();
    if ($user)
    {
        $format = $user->format_date;
    }

    if ($format === 'abr')
    {
        return StrDateformatClair($date, 1);
    }
    elseif ($format === 'clair')
    {
        return StrDateformatClair($date, 0);
    }
    elseif ($format === 'fr')
    {
        return StrDateformatFR($date, 0);
    }
    elseif ($format === 'fru')
    {
        return StrDateformatFR($date, 1);
    }
    else // format db
    {
        return $date;
    }
}


function StrDateformatClair ($date, $abrege)
{
    $formatted_date = "";

    if ($date == "0")
    {
        $formatted_date = "inconnue";
    }
    else if ($date == "n.i.")
    {
        $formatted_date = "non indiquée";
    }
    else if (($date == "A parution") || ($date == ""))
    {
        $formatted_date = $date;
    }
    else if (substr($date, 4) == "-circa")
    {
        if (substr($date, 0, 1) == "-")
        {
            $formatted_date = "c. " . substr($date, 1, 3) . " av. J.-C.";
        }
        else
        {
            $formatted_date = "c. " . substr($date, 0, 4);
        }
    }
    else if (substr($date, 0, 1) == "-")
    {
        $formatted_date = substr($date, 1, 3) . " av. J.-C.";
    }
    else if (substr($date, 5, 1) == "T")
    {
        if ($abrege == 1)
        {
            $formatted_date = StrTrimtoStrAbr(substr($date, 5, 2)) . " " . substr($date, 0, 4);
        }
        else
        {
            $formatted_date = StrTrimtoStr(substr($date, 5, 2)) . " " . substr($date, 0, 4);
        }
    }
    else if (substr($date, 5) == "00-00")
    {
        $formatted_date = substr($date, 0, 4);
    }
    else if (substr($date, 8) == "00")
    {
        if ($abrege == 1)
        {
            $formatted_date = StrMonthToStrAbr(substr($date, 5, 2)) . " " . substr($date, 0, 4);
        }
        else
        {
            $formatted_date = StrMonthToStr(substr($date, 5, 2)) . " " . substr($date, 0, 4);
        }
    }
    else
    {
        $jour = substr($date, 8, 2);
        if (substr($jour,0,1) == "0")
        {
            $jour = substr($jour,1,1);
        }
        if ($jour == "1")
        {
            $jour = "1er";
        }
        if ($abrege == 1)
        {
            $formatted_date = $jour . " " . StrMonthToStrAbr(substr($date, 5, 2)) . " " . substr($date, 0, 4);
        }
        else
        {
            $formatted_date = $jour . " " . StrMonthToStr(substr($date, 5, 2)) . " " . substr($date, 0, 4);
        }
    }

    return $formatted_date;
}

function StrDateformatFR ($date, $uniforme)
{
    $formatted_date = "";

    if ($date == "0")
    {
        $formatted_date = "inconnue";
    }
    else if ($date == "n.i.")
    {
        $formatted_date = "non indiquée";
    }
    else if (($date == "A parution") || ($date == ""))
    {
        $formatted_date = $date;
    }
    else if (substr($date, 4) == "-circa")
    {
        if (substr($date, 0, 1) == "-")
        {
            $formatted_date = "circa " . substr($date, 1, 3) . " av. J.-C.";
        }
        else
        {
            $formatted_date = "circa " . substr($date, 0, 4);
        }
    }
    else if (substr($date, 0, 1) == "-")
    {
        $formatted_date = substr($date, 1, 3) . " av. J.-C.";
    }
    else if (substr($date, 5, 1) == "T")
    {
        $formatted_date = substr($date, 5, 2) . "/" . substr($date, 0, 4);
    }
    else if (substr($date, 5) == "00-00")
    {
        $formatted_date = substr($date, 0, 4);
    }
    else if (substr($date, 8) == "00")
    {
        $mois = substr($date, 5, 2);
        if ((substr($mois,0,1) == "0") && ($uniforme == 0))
        {
            $mois = substr($mois,1,1);
        }
        $formatted_date = $mois . "/" . substr($date, 0, 4);
    }
    else
    {
        $jour = substr($date, 8, 2);
        $mois = substr($date, 5, 2);
        if ((substr($jour,0,1) == "0") && ($uniforme == 0))
        {
            $jour = substr($jour,1,1);
        }
        if ((substr($mois,0,1) == "0") && ($uniforme == 0))
        {
            $mois = substr($mois,1,1);
        }
        $formatted_date = $jour . "/" . $mois . "/" . substr($date, 0, 4);
    }

    return $formatted_date;
}

function StrTrimToStrAbr ($trim)
{
    if ($trim == "T1") { return "1er trim."; }
    else if ($trim == "T2") { return "2ième trim."; }
    else if ($trim == "T3") { return "3ième trim."; }
    else if ($trim == "T4") { return "4ième trim."; }
    else { return "err."; }
}
function StrTrimToStr ($trim)
{
    if ($trim == "T1") { return "1er trimestre"; }
    else if ($trim == "T2") { return "2ième trimestre"; }
    else if ($trim == "T3") { return "3ième trimestre"; }
    else if ($trim == "T4") { return "4ième trimestre"; }
    else { return "err."; }
}

function StrMonthToStrAbr ($month)
{
    if ($month == "01") { return "janv."; }
    else if ($month == "02") { return "févr."; }
    else if ($month == "03") { return "mars"; }
    else if ($month == "04") { return "avril"; }
    else if ($month == "05") { return "mai"; }
    else if ($month == "06") { return "juin"; }
    else if ($month == "07") { return "juil."; }
    else if ($month == "08") { return "août"; }
    else if ($month == "09") { return "sept."; }
    else if ($month == "10") { return "oct."; }
    else if ($month == "11") { return "nov."; }
    else if ($month == "12") { return "déc."; }
}
function StrMonthToStr ($month)
{
    if ($month == "01") { return "janvier"; }
    else if ($month == "02") { return "février"; }
    else if ($month == "03") { return "mars"; }
    else if ($month == "04") { return "avril"; }
    else if ($month == "05") { return "mai"; }
    else if ($month == "06") { return "juin"; }
    else if ($month == "07") { return "juillet"; }
    else if ($month == "08") { return "août"; }
    else if ($month == "09") { return "septembre"; }
    else if ($month == "10") { return "octobre"; }
    else if ($month == "11") { return "novembre"; }
    else if ($month == "12") { return "décembre"; }
}

// Sur base du JS github : /hecaxmmx/ISBNConverter
function isbnCheckAndConvert ($isbn,$m = "convert") {
    $x = str_replace(" ","",$isbn); // On retire les éventuels espaces
    $x = str_replace("-","",$x); // On retire les eventuels tirets (dans le cas d'un ISBN)
    $x = ucfirst($x);

    if ((strlen($x) != 10) && (strlen($x) != 13))
    {
        if ($m == "check") {
            return "NOK";
        }
        else {
            return "Erreur d'ISBN, la longueur n'est pas valide.";
        }
    }
    else if ((preg_match('/^\d{9}[0-9X]$/', $x) == false) && ((preg_match('/^\d{13}$/', $x) == false)))
    {
        if ($m == "check") {
            return "NOK";
        }
        else {
            return "Erreur d'ISBN, il contient des caractères invalides.";
        }
    }

    if (strlen($x) === 10)
    {
        // Validate & convert a 10-digit ISBN
        // Test for 10-digit ISBNs:
        // Formulated number must be divisible by 11
        // 0234567899 is a valid number
        $total = 0;
        for ($pos=0; $pos<9; $pos++) {
            $total = $total + (substr($x, $pos, 1) * (10 - $pos));
        }

        // check digit
        $last = substr($x, 9, 1);
        if ($last == "X") { $last = 10; }

        // validate ISBN
        if (($total + $last) % 11 != 0) {   // modulo function gives remainder
            // Erreur de checksum
            $last = (11 - ($total % 11)) % 11;
            if ($last == 10) {
                $last = "X";
            }
            if ($m == "check") {
                return 'NOK';
            }
            else
            {
                return ("Erreur d'ISBN, le checksum final est invalide, il devrait être " . $last . ".");
            }
        }
        else if ($m == "check") {
            // Checksum correct, donc ISBN 10 correct
            return 'OK';
        }
        else {
            // convert the 10-digit ISBN to a 13-digit ISBN
            $isbnnum = "978" . substr($x, 0, 9);
            $total = 0;
            for ($pos=0; $pos<12; $pos++) {
                if (($pos % 2) == 0) { $y = 1; }
                else { $y = 3; }
                $total = $total + ((int)substr($x, $pos, 1) * (int)$y);
            }
            $checksum = (10 - ($total % 10)) % 10;
            $isbnnum = "978-" . substr($isbn, 0, 12) . "$checksum";
            return "ISBN 13 : $isbnnum";
        }
    }
    else
    {
        // Validate & convert a 13-digit ISBN
        // Test for 13-digit ISBNs
        // 9780234567890 is a valid number
        $total = 0;
        for ($pos=0; $pos<12; $pos++) {
            if (($pos % 2) == 0) { $y = 1; }
            else { $y = 3; }
            $total = $total + ((int)substr($x, $pos, 1) * (int)$y);
        }

        // check digit
        $last = substr($x, 12, 1);

        // validate ISBN
        if ((10 - ($total % 10)) % 10 != $last) {   // modulo function gives remainder
            $last = (10 - ($total % 10)) % 10;
            if ($m == "check") {
                return 'NOK';
            }
            else
            {
                return ("Erreur d'ISBN, le checksum final est invalide, il devrait être " . $last . ".");
            }
        }
        else if ($m == "check") {
            // Checksum correct, donc ISBN 13 correct
            return 'OK';
        }
        else if ((substr($x, 0, 3) != "978")) {
            // Un ISBN 13 ne débutant pas par 978 ne peut être converti
            return ("Cet ISBN ne peut pas être converti en ISBN10.");
        }
        else {
            // convert the 13-digit ISBN to a 10-digit ISBN
            $isbnnum = substr($x, 3, 9);

            $total = 0;
            for ($pos=0; $pos<9; $pos++) {
                $total = $total + (substr($isbnnum, $pos, 1) * (10-$pos));
            }
            $checksum = (11 - ($total % 11)) % 11;
            if ($checksum == 10) { $checksum = "X"; }
            $isbnnum = substr($isbn, 4, 12) . "$checksum";
            return "ISBN 10 : " . $isbnnum;
        }
    }

}

function remove_accents($string, $utf8=true) {
    if ( !preg_match('/[\x80-\xff]/', $string) )
        return $string;

    if ($utf8) {
        $chars = array(
        // Decompositions for Latin-1 Supplement
        chr(194).chr(170) => 'a', chr(194).chr(186) => 'o',
        chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
        chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
        chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
        chr(195).chr(134) => 'AE',chr(195).chr(135) => 'C',
        chr(195).chr(136) => 'E', chr(195).chr(137) => 'E',
        chr(195).chr(138) => 'E', chr(195).chr(139) => 'E',
        chr(195).chr(140) => 'I', chr(195).chr(141) => 'I',
        chr(195).chr(142) => 'I', chr(195).chr(143) => 'I',
        chr(195).chr(144) => 'D', chr(195).chr(145) => 'N',
        chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
        chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
        chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
        chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
        chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
        chr(195).chr(158) => 'TH',chr(195).chr(159) => 's',
        chr(195).chr(160) => 'a', chr(195).chr(161) => 'a',
        chr(195).chr(162) => 'a', chr(195).chr(163) => 'a',
        chr(195).chr(164) => 'a', chr(195).chr(165) => 'a',
        chr(195).chr(166) => 'ae',chr(195).chr(167) => 'c',
        chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
        chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
        chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
        chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
        chr(195).chr(176) => 'd', chr(195).chr(177) => 'n',
        chr(195).chr(178) => 'o', chr(195).chr(179) => 'o',
        chr(195).chr(180) => 'o', chr(195).chr(181) => 'o',
        chr(195).chr(182) => 'o', chr(195).chr(184) => 'o',
        chr(195).chr(185) => 'u', chr(195).chr(186) => 'u',
        chr(195).chr(187) => 'u', chr(195).chr(188) => 'u',
        chr(195).chr(189) => 'y', chr(195).chr(190) => 'th',
        chr(195).chr(191) => 'y', chr(195).chr(152) => 'O',
        // Decompositions for Latin Extended-A
        chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
        chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
        chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
        chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
        chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
        chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
        chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
        chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
        chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
        chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
        chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
        chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
        chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
        chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
        chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
        chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
        chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
        chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
        chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
        chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
        chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
        chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
        chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
        chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
        chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
        chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
        chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
        chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
        chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
        chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
        chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
        chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
        chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
        chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
        chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
        chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
        chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
        chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
        chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
        chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
        chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
        chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
        chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
        chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
        chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
        chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
        chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
        chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
        chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
        chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
        chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
        chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
        chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
        chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
        chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
        chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
        chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
        chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
        chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
        chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
        chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
        chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
        chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
        chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
        // Decompositions for Latin Extended-B
        chr(200).chr(152) => 'S', chr(200).chr(153) => 's',
        chr(200).chr(154) => 'T', chr(200).chr(155) => 't',
        // Euro Sign
        chr(226).chr(130).chr(172) => 'E',
        // GBP (Pound) Sign
        chr(194).chr(163) => '',
        // Vowels with diacritic (Vietnamese)
        // unmarked
        chr(198).chr(160) => 'O', chr(198).chr(161) => 'o',
        chr(198).chr(175) => 'U', chr(198).chr(176) => 'u',
        // grave accent
        chr(225).chr(186).chr(166) => 'A', chr(225).chr(186).chr(167) => 'a',
        chr(225).chr(186).chr(176) => 'A', chr(225).chr(186).chr(177) => 'a',
        chr(225).chr(187).chr(128) => 'E', chr(225).chr(187).chr(129) => 'e',
        chr(225).chr(187).chr(146) => 'O', chr(225).chr(187).chr(147) => 'o',
        chr(225).chr(187).chr(156) => 'O', chr(225).chr(187).chr(157) => 'o',
        chr(225).chr(187).chr(170) => 'U', chr(225).chr(187).chr(171) => 'u',
        chr(225).chr(187).chr(178) => 'Y', chr(225).chr(187).chr(179) => 'y',
        // hook
        chr(225).chr(186).chr(162) => 'A', chr(225).chr(186).chr(163) => 'a',
        chr(225).chr(186).chr(168) => 'A', chr(225).chr(186).chr(169) => 'a',
        chr(225).chr(186).chr(178) => 'A', chr(225).chr(186).chr(179) => 'a',
        chr(225).chr(186).chr(186) => 'E', chr(225).chr(186).chr(187) => 'e',
        chr(225).chr(187).chr(130) => 'E', chr(225).chr(187).chr(131) => 'e',
        chr(225).chr(187).chr(136) => 'I', chr(225).chr(187).chr(137) => 'i',
        chr(225).chr(187).chr(142) => 'O', chr(225).chr(187).chr(143) => 'o',
        chr(225).chr(187).chr(148) => 'O', chr(225).chr(187).chr(149) => 'o',
        chr(225).chr(187).chr(158) => 'O', chr(225).chr(187).chr(159) => 'o',
        chr(225).chr(187).chr(166) => 'U', chr(225).chr(187).chr(167) => 'u',
        chr(225).chr(187).chr(172) => 'U', chr(225).chr(187).chr(173) => 'u',
        chr(225).chr(187).chr(182) => 'Y', chr(225).chr(187).chr(183) => 'y',
        // tilde
        chr(225).chr(186).chr(170) => 'A', chr(225).chr(186).chr(171) => 'a',
        chr(225).chr(186).chr(180) => 'A', chr(225).chr(186).chr(181) => 'a',
        chr(225).chr(186).chr(188) => 'E', chr(225).chr(186).chr(189) => 'e',
        chr(225).chr(187).chr(132) => 'E', chr(225).chr(187).chr(133) => 'e',
        chr(225).chr(187).chr(150) => 'O', chr(225).chr(187).chr(151) => 'o',
        chr(225).chr(187).chr(160) => 'O', chr(225).chr(187).chr(161) => 'o',
        chr(225).chr(187).chr(174) => 'U', chr(225).chr(187).chr(175) => 'u',
        chr(225).chr(187).chr(184) => 'Y', chr(225).chr(187).chr(185) => 'y',
        // acute accent
        chr(225).chr(186).chr(164) => 'A', chr(225).chr(186).chr(165) => 'a',
        chr(225).chr(186).chr(174) => 'A', chr(225).chr(186).chr(175) => 'a',
        chr(225).chr(186).chr(190) => 'E', chr(225).chr(186).chr(191) => 'e',
        chr(225).chr(187).chr(144) => 'O', chr(225).chr(187).chr(145) => 'o',
        chr(225).chr(187).chr(154) => 'O', chr(225).chr(187).chr(155) => 'o',
        chr(225).chr(187).chr(168) => 'U', chr(225).chr(187).chr(169) => 'u',
        // dot below
        chr(225).chr(186).chr(160) => 'A', chr(225).chr(186).chr(161) => 'a',
        chr(225).chr(186).chr(172) => 'A', chr(225).chr(186).chr(173) => 'a',
        chr(225).chr(186).chr(182) => 'A', chr(225).chr(186).chr(183) => 'a',
        chr(225).chr(186).chr(184) => 'E', chr(225).chr(186).chr(185) => 'e',
        chr(225).chr(187).chr(134) => 'E', chr(225).chr(187).chr(135) => 'e',
        chr(225).chr(187).chr(138) => 'I', chr(225).chr(187).chr(139) => 'i',
        chr(225).chr(187).chr(140) => 'O', chr(225).chr(187).chr(141) => 'o',
        chr(225).chr(187).chr(152) => 'O', chr(225).chr(187).chr(153) => 'o',
        chr(225).chr(187).chr(162) => 'O', chr(225).chr(187).chr(163) => 'o',
        chr(225).chr(187).chr(164) => 'U', chr(225).chr(187).chr(165) => 'u',
        chr(225).chr(187).chr(176) => 'U', chr(225).chr(187).chr(177) => 'u',
        chr(225).chr(187).chr(180) => 'Y', chr(225).chr(187).chr(181) => 'y',
        // Vowels with diacritic (Chinese, Hanyu Pinyin)
        chr(201).chr(145) => 'a',
        // macron
        chr(199).chr(149) => 'U', chr(199).chr(150) => 'u',
        // acute accent
        chr(199).chr(151) => 'U', chr(199).chr(152) => 'u',
        // caron
        chr(199).chr(141) => 'A', chr(199).chr(142) => 'a',
        chr(199).chr(143) => 'I', chr(199).chr(144) => 'i',
        chr(199).chr(145) => 'O', chr(199).chr(146) => 'o',
        chr(199).chr(147) => 'U', chr(199).chr(148) => 'u',
        chr(199).chr(153) => 'U', chr(199).chr(154) => 'u',
        // grave accent
        chr(199).chr(155) => 'U', chr(199).chr(156) => 'u',
        );

        // Used for locale-specific rules
        $locale = get_locale();

        if ( 'de_DE' == $locale ) {
            $chars[ chr(195).chr(132) ] = 'Ae';
            $chars[ chr(195).chr(164) ] = 'ae';
            $chars[ chr(195).chr(150) ] = 'Oe';
            $chars[ chr(195).chr(182) ] = 'oe';
            $chars[ chr(195).chr(156) ] = 'Ue';
            $chars[ chr(195).chr(188) ] = 'ue';
            $chars[ chr(195).chr(159) ] = 'ss';
        } elseif ( 'da_DK' === $locale ) {
            $chars[ chr(195).chr(134) ] = 'Ae';
            $chars[ chr(195).chr(166) ] = 'ae';
            $chars[ chr(195).chr(152) ] = 'Oe';
            $chars[ chr(195).chr(184) ] = 'oe';
            $chars[ chr(195).chr(133) ] = 'Aa';
            $chars[ chr(195).chr(165) ] = 'aa';
        }

        $string = strtr($string, $chars);
    } else {
        // Assume ISO-8859-1 if not UTF-8
        $chars['in'] = chr(128).chr(131).chr(138).chr(142).chr(154).chr(158)
            .chr(159).chr(162).chr(165).chr(181).chr(192).chr(193).chr(194)
            .chr(195).chr(196).chr(197).chr(199).chr(200).chr(201).chr(202)
            .chr(203).chr(204).chr(205).chr(206).chr(207).chr(209).chr(210)
            .chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218)
            .chr(219).chr(220).chr(221).chr(224).chr(225).chr(226).chr(227)
            .chr(228).chr(229).chr(231).chr(232).chr(233).chr(234).chr(235)
            .chr(236).chr(237).chr(238).chr(239).chr(241).chr(242).chr(243)
            .chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251)
            .chr(252).chr(253).chr(255);

        $chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

        $string = strtr($string, $chars['in'], $chars['out']);
        $double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
        $double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
        $string = str_replace($double_chars['in'], $double_chars['out'], $string);
    }

    return $string;
}

function get_locale() {
    global $locale;

    if ( empty( $locale ) ) {
        $locale = 'en_US';
    }

    /**
     * Filters the locale ID of the WordPress installation.
     *
     * @since 1.5.0
     *
     * @param string $locale The locale ID.
     */
    return $locale;
}

