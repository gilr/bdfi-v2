<?php

namespace App\Transformers\Types;

use App\Transformers\FormatTransformer;
use Illuminate\Http\UploadedFile;

class ColUtf8Transformer implements FormatTransformer
{

    /**
     *   Transformation vers le format pivot
    **/
    public function toArray(array|string|UploadedFile $input): array
    {
        if (is_array($input)) {
            return $input;
        }

        $lines = preg_split('/\r\n|\r|\n/', $input);
        $result = [];

        for ($i = 0; $i < count($lines); $i += 3) {
            $item = [];

            // Ligne 1 : "o INANNA     ?  2024.xx ..... ISBN 1234567890"
            if (preg_match('/ISBN\s+([^\s]+)/', $lines[$i], $matches)) {
                $item['isbn'] = $matches[1];
            }
            if (preg_match('/\?\s+(\d{4})\.xx/', $lines[$i], $matches)) {
                $item['annee_parution'] = $matches[1];
            }

            // Ligne 2 : "} couverture _illustrateur _N"
            if (preg_match('/}\s+([^\s]+)\s+_([^\s]{1,27})\s+_N/', $lines[$i+1], $matches)) {
                $item['img_fichier'] = trim($matches[1]);
                $item['img_couv'] = $item['img_fichier'];
                // TODO : spliter les couves

                $item['illustrateur'] = trim($matches[2]);
            }

            // Ligne optionnelle 2bis : !--- + informations
            // TO DO

            // Ligne 3 : "- .! .     R     _auteur titre [serie] ■copyright"
            if (preg_match('/- \.(.) \.     R     _(.{28})(.*?)(?: \[(.*?)\])? ?■(.*)/', $lines[$i+2], $matches)) {
                $item['est_genre'] = ($matches[1] == 'o');

                // Auteur : nom en majuscule, prénom en minuscule
                $auteur = trim($matches[2]);
                $auteurParts = preg_split('/\s+/', $auteur, 2);
                $item["auteurs"]["0"]['auteur_nom'] = isset($auteurParts[0]) ? $auteurParts[0] : '';
                $item["auteurs"]["0"]['auteur_prenom'] = isset($auteurParts[1]) ? $auteurParts[1] : '';

                $item['titre'] = trim($matches[3]);
                $item['serie'] = isset($matches[4]) ? $matches[4] : '';
                $item['sommaire'][0]['copyright'] = trim($matches[5]);
            }

            $result[] = $item;
        }

        return $result;
    }

    /**
     *   Transformation depuis le format pivot
    **/
    public function fromArray(array $data): string|array
    {
        $lines = [];

        //dd($data);
        foreach ($data as $item) {
            $est_genre      = $item['est_genre'] ?? '';
            $genre          = $item['genre'] ?? '';
            $prenom         = $item["auteurs"]["0"]['auteur_prenom'] ?? '';
            $nom            = $item["auteurs"]["0"]['auteur_nom'] ?? '';
            $titre          = $item['titre'] ?? '';
            $serie          = $item['serie'] ?? '';
            $num_serie      = $item['num_serie'] ?? '';
            $isbn           = $item['isbn'] ?? '';
            $illustrateur   = $item['illustrateur_couv'] ?? '';
            $illustrateurs  = $item['illustrateurs'] ?? '';
            $annee          = $item['annee_parution'] ?? '';
            $mois           = $item['mois_parution'] ?? '';
            $img_fichier    = $item['img_fichier'] ?? '';
            $copyright      = $item['sommaire']["0"]['copyright'] ?? '';
            $titre_vo      = $item['sommaire']["0"]['titre_vo'] ?? '';

            // Ligne éditeur/collection, date et ISBN - Format v1
            // TODO : éditeur/collection -> sigle
            // TODO : le numéro dans la collection
            // TODO : "o" ou "+" si réédition (quid du "x" ?)
            $lines[] = sprintf("o %-7s     ?  %4d.%02s ..... ISBN %s", "INANNA", $annee, $mois, $isbn);

            // Ligne couverture et illustrateurs - Format v1
            $lines[] = sprintf(
                "} %-14s _%-27s _%s",
                $img_fichier,
                $illustrateur,
                ($illustrateurs !== "" ? $illustrateurs : "N")
            );

            // Ligne data ouvrage - Format v1
            // TO DO
            $tampon = "!---";
            if (($item['audience'] === 'jeunesse') || ($item['audience'] === 'YA')) {
                $tampon = $tampon . " " . $item['audience'] . " -";
            }
            if ($item['audience'] === 'adulte-only') {
                $tampon = $tampon . " 18+ -";
            }
            if ($item['format'] !== '') { $tampon = $tampon . " " . $item['format'] . " -"; }
            if ($item['verifie_par'] !== '') { $tampon = $tampon . " " . $item['verifie_par'] . " -"; }
            if ($item['dimensions'] !== '') { $tampon = $tampon . " " . $item['dimensions'] . " mm -"; }
            if ($item['approximate_pages'] !== '') { $tampon = $tampon . " " . $item['approximate_pages'] . " pages -"; }
            if ($item['dl'] !== '') { $tampon = $tampon . " DL " . $item['dl'] . " -"; }
            if ($item['ai'] !== '') { $tampon = $tampon . " AI " . $item['ai'] . " -"; }
            if ($item['imprimeur'] !== '') { $tampon = $tampon . " IMPRIM " . $item['imprimeur'] . " -"; }
            // TODO : ajouter les DPI, DPU et PTO si besoin d'export en .col depuis la base
            // TODO : ajouter l'info prix
            if ($tampon !== "!---") {
                $lines[] = $tampon;
            }

            // Ligne description du contenant - Format v1
            $col_est_genre = ($est_genre === "oui" ? "o" : "!");
            $col_genre =
                ($genre === "sf" ? "S" :
                    ($genre === "fantasy" ? "Y" :
                        ($genre === "fantastique" ? "F" :
                            ($genre === "imaginaire" ? "I" : " ")
                        )
                    )
                );
            if ($num_serie === "")
            {
                $cycle = ($serie === "" ? "" : " [$serie]");
            }
            else
            {
                $cycle = ($serie === "" ? "" : " [$serie - $num_serie]");
            }
            $auteur = ($nom === "" ?  "$prenom" : strtoupper($nom) . " $prenom");
            if ($titre_vo !== "") { $titre_vo = " " . $titre_vo; }
            // TODO traducteurs ([]Trad. noms)
            $traducteurs = "";
            $lines[] = sprintf(
                "- .%1s%1s.     R     _%-28s%s%s %1s%s%s%s",
                $col_est_genre,
                $col_genre,
                $auteur,
                $titre,
                $cycle,
                "■",
                $copyright,
                $titre_vo,
                $traducteurs
            );
        }

        return implode(PHP_EOL, $lines);
    }

}
