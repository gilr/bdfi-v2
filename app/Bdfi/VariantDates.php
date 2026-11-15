<?php

namespace App\Bdfi;

/**
 * Comparaison des dates de parution fr (champ copyright_fr) entre un texte et son parent.
 */
class VariantDates
{
    /**
     * Période couverte par une date BDFI, en [début, fin] comparables (AAAAMMJJ) : AAAA-MM-JJ avec
     * 00 si mois ou jour inconnus, AAAA-Tn-00 pour un trimestre, AAAA-circa. Null si date inconnue.
     * @return array{0: int, 1: int}|null
     */
    public static function range(?string $date): ?array
    {
        if (!preg_match('/^(\d{4})(?:-(\d{2}|T[1-4])(?:-(\d{2}))?)?/', (string) $date, $m) || (int) $m[1] === 0) {
            return null;
        }

        $year = (int) $m[1];
        $month = $m[2] ?? '00';
        $day = (int) ($m[3] ?? 0);

        if ($month[0] === 'T') {
            $quarter = (int) $month[1];
            [$from, $to] = [($quarter - 1) * 3 + 1, $quarter * 3];
        } elseif ((int) $month === 0) {
            [$from, $to] = [1, 12];
        } else {
            $from = $to = (int) $month;
        }
        $exactDay = $from === $to && $day > 0;

        return [
            $year * 10000 + $from * 100 + ($exactDay ? $day : 0),
            $year * 10000 + $to * 100 + ($exactDay ? $day : 31),
        ];
    }

    /** Antérieure seulement si c'est certain : la fin de la période du texte précède le début de celle du parent */
    public static function isEarlier(?string $childDate, ?string $parentDate): bool
    {
        $child = self::range($childDate);
        $parent = self::range($parentDate);

        return $child && $parent && $child[1] < $parent[0];
    }
}
