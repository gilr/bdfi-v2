<?php

namespace App\Bdfi\BookConverter;

/**
 * Conversions de champs communes aux converters (dates DL/AI, images de couverture...).
 * Utilisé par ColConverter ; CsvConverter peut l'adopter en supprimant ses copies de ces méthodes.
 */
trait ConvertsBookFields
{
    /** trim, et "_" (marqueur "pas de valeur") => chaîne vide */
    protected function clean(?string $value): string
    {
        $value = trim((string) $value);

        return $value === '_' ? '' : $value;
    }

    /** Mois (minuscules) => "MM" ou "Tn" (trimestre) */
    protected function monthMap(): array
    {
        return [
            'january' => '01', 'jan' => '01', 'janv' => '01', 'janv.' => '01', 'janvier' => '01',
            'february' => '02', 'feb' => '02', 'fev' => '02', 'fevr' => '02', 'fevr.' => '02', 'fév' => '02', 'févr' => '02', 'févr.' => '02', 'fevrier' => '02', 'février' => '02',
            'march' => '03', 'mar' => '03', 'mars' => '03',
            'april' => '04', 'apr' => '04', 'avr' => '04', 'avr.' => '04', 'avril' => '04',
            'mai' => '05', 'may' => '05',
            'june' => '06', 'jun' => '06', 'juin' => '06',
            'july' => '07', 'jul' => '07', 'juil' => '07', 'juil.' => '07', 'juillet' => '07',
            'august' => '08', 'aug' => '08', 'aou' => '08', 'aout' => '08', 'août' => '08',
            'september' => '09', 'sep' => '09', 'sept' => '09', 'sept.' => '09', 'septembre' => '09',
            'october' => '10', 'oct' => '10', 'oct.' => '10', 'octobre' => '10',
            'november' => '11', 'nov' => '11', 'nov.' => '11', 'novembre' => '11',
            'december' => '12', 'dec' => '12', 'dec.' => '12', 'déc' => '12', 'déc.' => '12', 'decembre' => '12', 'décembre' => '12',
            't1' => 'T1', '1t' => 'T1',
            't2' => 'T2', '2t' => 'T2',
            't3' => 'T3', '3t' => 'T3',
            't4' => 'T4', '4t' => 'T4',
        ];
    }

    /** "3", "03", "mars", "T2"... => "MM" ou "Tn" ; null si non reconnu */
    protected function normalizeMonth(string $value): ?string
    {
        if (preg_match('/^(0?[0-9]|1[0-2])$/', $value)) {
            return str_pad($value, 2, '0', STR_PAD_LEFT);
        }

        return $this->monthMap()[$value] ?? null;
    }

    /** Année sur 2 chiffres => 4 chiffres : le siècle qui rapproche le plus de l'année de parution estimée */
    protected function expandYear(string $year, string $approxYear): string
    {
        if (strlen($year) === 4) {
            return $year;
        }

        $yy = (int) $year;

        if (preg_match('/^\d{4}$/', $approxYear)) {
            $ref = (int) $approxYear;
            $base = intdiv($ref, 100) * 100;
            $candidates = [$base - 100 + $yy, $base + $yy, $base + 100 + $yy];
            usort($candidates, fn ($a, $b) => abs($a - $ref) <=> abs($b - $ref));

            return (string) $candidates[0];
        }

        return (string) ($yy > (int) date('y') + 1 ? 1900 + $yy : 2000 + $yy);
    }

    /**
     * Dépôt légal / achevé d'imprimer => "AAAA-MM-JJ" (00 = inconnu, "Tn" = trimestre).
     * Formats acceptés : "A parution", "n.i.", "1951", "03/1951", "mars 1951", "Aug-02", "12/03/51",
     * "1951-03-12", "2ème trimestre 1951"... Valeur non décodable => "Err Decod." (10 caractères).
     * $approxParution ("AAAA-MM-00") sert à deviner le siècle des années sur 2 chiffres.
     */
    protected function convertDlAi(string $value, string $approxParution): string
    {
        $value = mb_strtolower(trim($value));

        if ($value === '') {
            return '';
        }

        $special = [
            'a parution' => 'A parution',
            'à parution' => 'A parution',
            'n.i.' => 'Non indiq.',
            'n.c.' => 'Non indiq.',
        ];
        if (isset($special[$value])) {
            return $special[$value];
        }

        $approxYear = substr($approxParution, 0, 4);

        if (preg_match('/^([1-4])\s*(?:er|ère|e|ème|eme|ième|ieme)?\s*trimestre\s+(\d{2}|\d{4})$/u', $value, $m)) {
            return $this->expandYear($m[2], $approxYear) . "-T{$m[1]}-00";
        }

        $parts = preg_split('/[\/\s-]+/', $value, -1, PREG_SPLIT_NO_EMPTY);

        [$day, $month, $year] = match (count($parts)) {
            1 => ['00', '00', $parts[0]],
            2 => strlen($parts[0]) === 4 ? ['00', $parts[1], $parts[0]] : ['00', $parts[0], $parts[1]],
            3 => strlen($parts[0]) === 4 ? [$parts[2], $parts[1], $parts[0]] : [$parts[0], $parts[1], $parts[2]],
            default => [null, null, null],
        };

        $month = $month === null ? null : $this->normalizeMonth($month);
        $day = ($day !== null && preg_match('/^\d{1,2}$/', $day)) ? str_pad($day, 2, '0', STR_PAD_LEFT) : null;

        if ($year !== null && preg_match('/^\d{2}$/', $year)) {
            $year = $this->expandYear($year, $approxYear);
        }

        if ($year === null || !preg_match('/^\d{4}$/', $year) || $month === null || $day === null) {
            return 'Err Decod.';
        }

        return "{$year}-{$month}-{$day}";
    }

    /**
     * Nom d'image => images du livre. Lettres finales (ordre quelconque) : v verso, d dos, b face avec
     * bandeau, j face avec jaquette. Un "_" final = image unique dont le nom finit naturellement par
     * l'une de ces lettres (le "_" est retiré, c'est la couverture seule).
     */
    protected function getCoverImages(string $input): array
    {
        $images = [
            'img_couv'     => $input,
            'img_back'     => null,
            'img_dos'      => null,
            'img_bandeau'  => null,
            'img_jaquette' => null,
        ];

        if (str_ends_with($input, '_')) {
            $images['img_couv'] = substr($input, 0, -1);

            return $images;
        }

        if (preg_match('/^(.*?)([bvdj]+)$/', $input, $m) && $m[1] !== '') {
            $keys = ['v' => 'img_back', 'd' => 'img_dos', 'b' => 'img_bandeau', 'j' => 'img_jaquette'];

            $images['img_couv'] = $m[1];
            foreach (array_unique(str_split($m[2])) as $letter) {
                $images[$keys[$letter]] = $m[1] . $letter;
            }
        }

        return $images;
    }
}