<?php


/**
 * Formatage noms et autres formes
 *
 * @return string
 */
function sanitizeFirstName ($firstname)
{
   $pattern = "/\.[A-Z]/i";
   if (preg_match($pattern, $firstname))
   {
      return str_replace('.', '. ', $firstname);
   }
   else
   {
      return $firstname;
   }
}

function displayAuthorBiblio ($analyse, $results, $title)
{
   if (($title->authors->count() == 0))
   {
      // Cas spécial : pas d'auteur référencé sur cette oeuvre/variante
      if ($analyse == 0)
      {
         return "<span class='bg-lime-400 font-normal text-xs italic'>K.0 </span>";
      }
      else
      {
         return "<span class='font-semibold text-red-500'> Non crédité ou inconnu</span>";
      }
   }
   elseif (($title->authors->count() == 1) &&
       ($results->id == $title->authors[0]->id))
   {
      // Cas 1 : une seule signature, et identique
      if ($analyse == 0)
      {
         return "<span class='bg-lime-400 font-normal text-xs italic'>K.1 </span>";
      }
      else
      {
         return "";
      }
   }
   elseif (($title->authors->count() == 1) &&
       ($results->id != $title->authors[0]->id))
   {
      if ($analyse == 0)
      {
         return "<span class='bg-lime-400 font-normal text-xs italic'>K.2 </span>";
      }
      else
      {
         // Cas 2 : une seule signature, mais différente
         $id = $title->authors[0]->id;
         $nom = $title->authors[0]->fullname;
         $credit = " signé <a class='text-red-800 border-b border-dotted border-purple-700 hover:text-purple-700' href='/auteurs/$id'>$nom</a>";
         return "$credit";
      }
   }
   else
   {

      // Si l'auteur est dans la liste
      $trouve = 0;
      foreach($title->authors as $author)
      {
         if ($author->id == $results->id)
         {
            $trouve = 1;
         }
      }

      if ($trouve == 1)
      {
         if ($analyse == 0)
         {
            return "<span class='bg-lime-400 font-normal text-xs italic'>K.3 </span>";
         }
         else
         {
            $credit = " avec ";
            foreach($title->authors as $author)
            {
               if ($author->id != $results->id)
               {
                  $credit = $credit . " <a class='text-red-800 border-b border-dotted border-purple-700 hover:text-purple-700' href='/auteurs/$author->id'>$author->fullname</a>";
               }
            }
            return "$credit";
         }
      }
      else
      {
         if ($analyse == 0)
         {
            return "<span class='bg-lime-400 font-normal text-xs italic'>K.4 </span>";
         }
         else
         {
            // Liste d'auteurs/anthologistes sans le nom initial
            $credit = "";
            foreach($title->authors as $author)
            {
               $credit = $credit . " <a class='text-red-800 border-b border-dotted border-purple-700 hover:text-purple-700' href='/auteurs/$author->id'>$author->fullname</a>";
            }
            return "$credit";
         }
      }
   }

}

/**
 * Formatage noms et autres formes
 *
 * @return string
 */
function formatAuthorNames ($nbrefs, $nom_bdfi, $prenom, $nom, $pseu, $legal, $formes)
{
   $tip = ($nbrefs == 1 ? '' : '&nbsp;&rarr;&nbsp;'); 

   $pattern = "";

  // Les autres données ne doivent pas être remplies -> A FAIRE, outils de vérif

   // A FAIRE : si nbrefs > 2 : format avec lien sur l'URL de la page auteur ici
   $bdfi = "$nom $prenom";
   $normal = str_replace("' ", "'", "$prenom $nom");
   $normal = str_replace(" $", "", $normal);
   if (($nbrefs > 2) && (($link = check_page($nom_bdfi)) != false))
   {
      $pattern .= "<em>$tip ";
      $pattern .= '<a href="' . $link . '">' . $normal . '</a>';
      $pattern .= "</em>";
   }
   else
   {
      $pattern .= "<em><strong>$tip $normal</strong></em>";
   }  
   $pattern .= ($nbrefs > 2 ? ' - ' : "<br />\n"); 

   // #------------------------------------------------------------
   // # Pseudonyme, indiquer le vrai nom
   // #------------------------------------------------------------
   if ($pseu == 1)
   {
      $pattern .= "Pseudonyme";
      // TBD : mettre les 'et' en non gras
      // Indiquer les liens si ils existent
      if (($legal != '') && ($legal != '?'))
      {
         $pattern .= " de <b>$legal</b>";
      }
      $pattern .= ($nbrefs > 2 ? ' - ' : "<br><br />\n"); 
   }
   else
   {
      if (($legal != '') && ($legal != $normal))
      {
         $pattern .= "Nom légal : $legal";
         $pattern .= ($nbrefs > 2 ? ' - ' : "<br />\n"); 
      }
   }

   // #------------------------------------------------------------
   // # Ici, on pourrait mettre le lien sur le vrai nom, si la page existe
   // #------------------------------------------------------------
   if (($formes != '') && ($nbrefs < 3))
   {
      $pattern .= "Autre(s) forme(s) : $formes<br />";
   }

   return $pattern;
}

/**
 * Formatage des dates et lieux de naissance et décès
 *
 * @return string
 */
function formatAuthorDates ($gender, $birth_date, $date_death, $birthplace, $deathplace)
{
   $pattern = "";

   if ($birth_date === NULL) { $birth_date = "0000-00-00"; }
   if ($date_death === NULL) { $date_death = "0000-00-00"; }

   if ($birth_date != "0000-00-00")
   {
      $pattern = "<br />" . formatBdfiDate($gender, $birth_date, 1, $birthplace) . "\n";
   }
   if ($date_death != "0000-00-00")
   {
      $pattern .= "<br />" . formatBdfiDate($gender, $date_death, 2, $deathplace) . "\n";
   }
   return $pattern;
}
function oldFormatAuthorDates ($gender, $birth_date, $date_death, $deathplace)
{
   $pattern = "";

   if ($birth_date === NULL) { $birth_date = "0000-00-00"; }
   if ($date_death === NULL) { $date_death = "0000-00-00"; }

   if (($birth_date != "0000-00-00") && ($date_death != "0000-00-00"))
   {
      $pattern = " (" . (($birthplace != "") ? $birthplace . ", " : ""); 
      $pattern .= formatBdfiDate($gender, $birth_date, 1) . " - " . formatBdfiDate($gender, $date_death, 2) . ")\n";
   }
   else if ($birth_date != "0000-00-00")
   {
      $pattern = " (" . formatBdfiDate($gender, $birth_date, 1, $birthplace) . ")\n";
   }
   else if ($date_death != "0000-00-00")
   {
      $pattern = " (" . formatBdfiDate($gender, $date_death, 2, $deathplace) . ")\n";
   }
   return $pattern;
}

/**
 * Formatage d'une date simple
 *
 * @return string
 */
function formatBdfiDate ($gender, $str, $mode, $place="")
{
   $ne = ($gender == App\Enums\AuthorGender::F ? "Née" : "Né");
   $decede = ($gender == App\Enums\AuthorGender::F ? "Décédée" : "Décédé");
   $place = ($place != '?') ? $place : '';
   
   $tabmois = array (
      '01' => 'janvier',
      '02' => 'février',
      '03' => 'mars',
      '04' => 'avril',
      '05' => 'mai',
      '06' => 'juin',
      '07' => 'juillet',
      '08' => 'août',
      '09' => 'septembre',
      '10' => 'octobre',
      '11' => 'novembre',
      '12' => 'décembre',
   );
   $avjc="";
   if ($str[0] == "-") 
   {
      $avjc = " av. J.-C.";
      $str = substr($str, 1);
   }
   $amj = explode("-", $str);
   $an  = number_format((float)$amj[0], 0, ".", "");
   $mois = $amj[1];
   $jour = ($mois == "circa" ? "00" : $amj[2]);

   if ($mode == 0) 
   {
      // Extraction format date dans un mode ou les deux dates sont connues
      if ($mois == 'circa')
      {
         return "vers $an" . $avjc;
      }
      else if (($jour != '00') && ($mois != '00'))
      {
         return "$jour/$mois/$an";
      }
      else if ($mois != '00')
      {
         return $tabmois[$mois] . " $an";
      }
      else
      {
         return "$an";
      }
   }
   else if ($mode == 1)
   {
      // Extraction date de naissance (si date décés inconnue)
      if ($mois == 'circa')
      {
         return "$ne vers $an " . $avjc . ($place != '' ? " à $place": '');
      }
      else if (($jour != '00') && ($mois != '00'))
      {
         return "$ne le $jour/$mois/$an" . ($place != '' ? " à $place": '');
      }
      else if ($mois != '00')
      {
         return "$ne en " . $tabmois[$mois] . " $an" . ($place != '' ? " à $place" : '');
      }
      else
      {
         return "$ne en $an" . ($place != '' ? " à $place" : '');
      }
   }
   else if ($mode == 2)
   {
      // Extraction date de décès (si date naissance inconnue)
      if ($mois == 'circa')
      {
         return ($place != '' ? "$ne à $place, ": "") . "$decede vers $an" . $avjc;
      }
      else if (($jour != '00') && ($mois != '00'))
      {
         return ($place != '' ? "$ne à $place, ": "") . "$decede le $jour/$mois/$an";
      }
      else if ($mois != '00')
      {
         return ($place != '' ? "$ne à $place, ": "") . "$decede en " . $tabmois[$mois] . " $an";
      }
      else
      {
         return ($place != '' ? "$ne à $place, ": "") . "$decede en $an";
      }
   }
}

function awardAuthors ($name, $id1, $id2, $id3)
{
   $nom1 = $nom2 = $nom3 = "";
   $noms = explode(" et ", $name, 2);

   $noms2 = explode(", ", $noms[0], 2);
   $nom1 = $noms2[0];

   if (isset($noms2[1])) {
      $nom2 = $noms2[1];
      $nom3 = $noms[1];
   }
   else if (isset($noms[1])) {
      $nom2 = $noms[1];
   }

   if ($nom1 != "") {
      $result = formatAuthor ($nom1, $id1);
   }
   if ($nom3 != "") {
      $result = $result . ", " . formatAuthor ($nom2, $id2);
      $result = $result . " et " . formatAuthor ($nom3, $id3);
   }
   else if ($nom2 != "") {
      $result = $result . " et " . formatAuthor ($nom2, $id2);
   }
   return $result;
}

function formatAuthor ($name, $id)
{
      if ($id != NULL) {
         return "<a class='text-red-800 border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/auteurs/$id'>$name</a>";
      }
      else {
         return "<span class='text-red-950 italic'>$name</span>";
      }
}

function StrConvTrad($name)
{

   $morceaux = explode(" ", $name);
   if (count($morceaux) == 1)
   {
      return ucfirst(strtolower($name));
   }

   // TODO : simpliste pour l'instant
   $morceaux = explode(" ", $name, 2);

   return $morceaux[1] . " " . ucfirst(strtolower($morceaux[0]));

}

function normalizeTraducteurs($info) {
    $info = preg_replace('/ \+ /', ', ', $info);
    $info = preg_replace('/ & /', ', ', $info);
    $info = preg_replace('/ et /', ', ', $info);
//   $info = str_replace(' + ', ', ', $info);
//   $info = str_replace(' & ', ', ', $info);
//   $info = str_replace(' et ', ', ', $info);

    $trads = explode(', ', $info);
    $new_trads = array();
    foreach ($trads as $trad) {
        $new_trad = reverseName($trad);
        array_push($new_trads, $new_trad);
    }
    $result = implode(', ', $new_trads);

    // non utile car déjà en amont
    // return oem2utf($result);
    return $result;
}

// Note: The function reverse_name and oem2utf ne
function oem2utf($chaine) {
    $win = iconv('CP437', 'UTF-8', $chaine);
    return $win;
}

function reverseName($info)
{
    // Trim the string to remove spaces from the beginning and end
   $info = trim($info);
   $info = preg_replace('/ +$/', '', $info);
   $info = preg_replace('/^ +/', '', $info);

   $mots = explode(' ', $info);
   $count = count($mots);
   if ($count == 1)
   {
      $result = ucfirst(strtolower($info));
   }
   else
   {
      //TODO
      // On répète 4 fois (le max de "nom" actuel dans ma base) / ou pour chaque morceau de nom pour simplifier :
      // Si le premier morceau de nom fait partie du nom, le décaler en fin
      for ($i=0 ; $i<$count ; $i++) {
         if (isName($morceau = array_shift($mots)) == 0)
         {
            array_unshift($mots, ucfirst(strtolower($morceau)));
         }
         else
         {
            array_push($mots, ucfirst(strtolower($morceau)));
         }
      }
      $result = implode(' ', $mots);
   }

   // ... Et mettre en majuscule toute lettre qui suit un "-".
   return capitalizeAfterDash($result);
}

function capitalizeAfterDash($string) {
    // Utilisation de la fonction preg_replace_callback pour trouver les tirets et la lettre suivante
    return preg_replace_callback('/-(.)/', function($matches) {
        // strtoupper convertit la lettre en majuscule
        return '-' . strtoupper($matches[1]);
    }, $string);
}

function isName($info)
{
    // Si le nom fait deux lettres, toutes deux majuscules
    // ... ou si le nom fait plus de deux, avec la première et la troisième majuscule
    if ((strlen($info) == 2) &&
        ($info[0] >= 'A') && ($info[0] <= 'Z') &&
        ($info[1] >= 'A') && ($info[1] <= 'Z')) {
        return 1;
    } elseif ((strlen($info) > 2) &&
        ($info[0] >= 'A') && ($info[0] <= 'Z') &&
        ($info[2] >= 'A') && ($info[2] <= 'Z')) {
        return 1;
    }
    return 0;
}
