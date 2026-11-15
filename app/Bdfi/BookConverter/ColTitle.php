<?php

namespace App\Bdfi\BookConverter;

/**
 * Décomposition d'un titre COL en titre, alias de recueil et cycles.
 * Portage de decomp_titre() du module Perl bdfi.pm (v2.1), mêmes règles :
 *
 *   Titre ((Alias recueil)) [cycles]
 *
 *   [cycle1 - x]+[cycle2 - x]+[cycle3 - x]          cycles distincts (3 max), séparés par "+"
 *   [souscycle - x]<[cycle - x]  ou  [souscycle][cycle]   sous-cycle du premier cycle seulement
 *   [ssc - x][souscycle - x][cycle - x]             sous-sous-cycle (3 niveaux)
 *   [sc1 - x]|[sc2 - x]|[sc3 - x]<[cycle - x]       sous-cycles alternatifs (3 max), séparés par "|"
 *
 * Un titre qui commence par "[" n'a pas de cycle. "[[" et "]]" valent "[" et "]".
 */
final class ColTitle
{
    /**
     * @return array{titre: string, alias: string, cycles: array<int, array{nom: string, numero: string, role: string, parent: string}>}
     *         cycles du plus précis au plus général : sous-sous-cycle, sous-cycles, cycle, cycles 2 et 3.
     *         role : sous_sous_cycle, sous_cycle, cycle ; parent : nom du cycle parent ('' si aucun)
     * @throws \InvalidArgumentException plus de 3 cycles ou de 3 sous-cycles (le Perl s'arrêtait)
     */
    public static function decompose(string $raw): array
    {
        $raw = str_replace(['[[', ']]'], ['[', ']'], $raw);

        $title = $raw;
        $cycles = [];

        if (!str_starts_with($raw, '[')) {
            $parts = explode('[', $raw, 2);
            $title = rtrim($parts[0]);
            $pattern = rtrim($parts[1] ?? '');

            if ($pattern !== '') {
                $cycles = self::cycles('[' . $pattern);
            }
        }

        // Alias de recueil : "Titre ((Alias))"
        $parts = explode('((', $title, 2);
        $title = rtrim($parts[0]);
        $alias = isset($parts[1]) ? preg_replace('/\)\)$/u', '', rtrim($parts[1])) : '';

        return ['titre' => $title, 'alias' => $alias, 'cycles' => $cycles];
    }

    /** "[a]|[b]<[c - 1]+[d]" => liste ordonnée des cycles */
    private static function cycles(string $pattern): array
    {
        $items = explode(']+[', $pattern);

        if (count($items) > 3) {
            throw new \InvalidArgumentException("plus de 3 cycles : {$pattern}");
        }

        $subSub = null;
        $subs = [];
        $main = [];

        foreach ($items as $i => $item) {
            $item = preg_replace(['/^\[/u', '/\]$/u'], '', $item);

            if ($i > 0) {
                $main[] = self::cycle($item, 'cycle');
                continue;
            }

            // Premier cycle : sous-sous-cycle, liste de sous-cycles, cycle (séparés par "][" ou "]<[")
            $pieces = preg_split('/\]<?\[/u', $item, 3);
            [$a, $b, $c] = array_pad($pieces, 3, '');

            if ($c === '') {        // 2 éléments : sous-cycle(s) + cycle
                [$a, $b, $c] = ['', $a, $b];
            }
            if ($c === '') {        // 1 élément : cycle seul
                [$a, $b, $c] = ['', '', $b];
            }

            if ($a !== '') {
                $subSub = self::cycle($a, 'sous_sous_cycle');
            }

            if ($b !== '') {
                $list = explode(']|[', $b);
                if (count($list) > 3) {
                    throw new \InvalidArgumentException("plus de 3 sous-cycles : {$pattern}");
                }
                foreach ($list as $sub) {
                    $subs[] = self::cycle(preg_replace(['/^\[/u', '/\]$/u'], '', $sub), 'sous_cycle');
                }
            }

            array_unshift($main, self::cycle($c, 'cycle'));
        }

        // Hiérarchie : sous-cycles => cycle du premier groupe ; sous-sous-cycle => (premier) sous-cycle.
        // Les cycles 2 et 3 sont indépendants (pas de parent).
        $firstCycle = $main[0]['nom'] ?? '';
        foreach ($subs as &$sub) {
            $sub['parent'] = $firstCycle;
        }
        unset($sub);
        if ($subSub) {
            $subSub['parent'] = $subs[0]['nom'] ?? $firstCycle;
        }

        return array_values(array_filter(
            array_merge($subSub ? [$subSub] : [], $subs, $main),
            fn ($c) => $c['nom'] !== '',
        ));
    }

    /** "Nom du cycle - 3" => nom + numéro (split sur le premier " - ", comme le Perl) */
    private static function cycle(string $value, string $role): array
    {
        $parts = explode(' - ', trim($value));

        return ['nom' => trim($parts[0]), 'numero' => trim($parts[1] ?? ''), 'role' => $role, 'parent' => ''];
    }
}
