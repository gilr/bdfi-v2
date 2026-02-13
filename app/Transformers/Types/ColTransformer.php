<?php

namespace App\Transformers\Types;

use App\Transformers\FormatTransformer;
use Illuminate\Http\UploadedFile;

class ColTransformer implements FormatTransformer
{
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
                $item['couverture'] = iconv('CP437', 'UTF-8//IGNORE', trim($matches[1]));
                $item['illustrateur'] = iconv('CP437', 'UTF-8//IGNORE', trim($matches[2]));
            }

            // Ligne 3 : "- .! .     R     _auteur titre [serie] ■copyright"
            if (preg_match('/- \.(.) \.     R     _(.{28})(.*?)(?: \[(.*?)\])? ?■(.*)/', $lines[$i+2], $matches)) {
                $item['hors_genres'] = ($matches[1] == '!');

                // Auteur : nom en majuscule, prénom en minuscule
                $auteur = iconv('CP437', 'UTF-8//IGNORE', trim($matches[2]));
                $auteurParts = preg_split('/\s+/', $auteur, 2);
                $item['auteur_nom'] = isset($auteurParts[0]) ? $auteurParts[0] : '';
                $item['auteur_prenom'] = isset($auteurParts[1]) ? $auteurParts[1] : '';

                $item['titre'] = iconv('CP437', 'UTF-8//IGNORE', trim($matches[3]));
                $item['serie'] = isset($matches[4]) ? iconv('CP437', 'UTF-8//IGNORE', $matches[4]) : '';
                $item['sommaire'][0]['copyright'] = iconv('CP437', 'UTF-8//IGNORE', trim($matches[5]));
            }

            $result[] = $item;
        }

        return $result;
    }

    public function fromArray(array $data): string|array
    {
        $lines = [];

        foreach ($data as $item) {
            $horsGenres     = $item['hors_genres'] ?? '';
            $prenom         = $item['auteur_prenom'] ?? '';
            $nom            = $item['auteur_nom'] ?? '';
            $titre          = $item['titre'] ?? '';
            $serie          = $item['serie'] ?? '';
            $copyright      = $item['sommaire'][0]['copyright'] ?? '';
            $isbn           = $item['isbn'] ?? '';
            $illustrateur   = $item['illustrateur'] ?? '';
            $annee          = $item['annee_parution'] ?? '';
            $couverture     = $item['couverture'] ?? '';

            $lines[] = iconv('UTF-8', 'CP437', sprintf("o %-7s     ?  %4d.xx ..... ISBN %s", "INANNA", $annee, $isbn));

            $illustrateurFormate = $this->padCp437($illustrateur, 27);
            $lines[] = sprintf(
                "} %-14s _%27s _N",
                iconv('UTF-8', 'CP437//TRANSLIT', $couverture),
                $illustrateurFormate
            );

            $genre = ($horsGenres == true ? "!" : "o");
            $cycle = ($serie === "" ? "" : " [$serie]");
            $auteur = strtoupper($nom) . " $prenom";
            $auteurFormate = $this->padCp437($auteur, 28);

            $lines[] = sprintf(
                "- .%1s .     R     _%-28s%s%s %1s%s",
                $genre,
                $auteurFormate,
                iconv('UTF-8', 'CP437//TRANSLIT', $titre),
                iconv('UTF-8', 'CP437//TRANSLIT', $cycle),
                iconv('UTF-8', 'CP437//TRANSLIT', "■"),
                iconv('UTF-8', 'CP437//TRANSLIT', $copyright)
            );
        }

        return implode(PHP_EOL, $lines);
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
