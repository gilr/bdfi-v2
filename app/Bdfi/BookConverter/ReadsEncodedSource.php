<?php

namespace App\Bdfi\BookConverter;

/**
 * Lecture d'un fichier source en UTF-8, quel que soit son encodage d'origine.
 * Utilisé par les converters (CSV, COL...) : ils appellent readSource() et n'ont plus à faire d'iconv.
 *
 * $encoding : 'auto' (défaut) | 'utf8' | tout nom d'encodage accepté par iconv ('cp437', 'cp1252', ...)
 * 'auto' => UTF-8 si le contenu est un UTF-8 valide, sinon CP437 (DOS).
 */
trait ReadsEncodedSource
{
    protected string $encoding = 'auto';

    protected function readSource(string $path): string
    {
        $content = file_get_contents($path);

        if ($content === false) {
            throw new \RuntimeException("Lecture impossible : {$path}");
        }

        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);        // BOM UTF-8 éventuel
        $encoding = strtolower($this->encoding);
        $isUtf8 = mb_check_encoding($content, 'UTF-8');

        if ($encoding === 'auto') {
            $encoding = $isUtf8 ? 'utf8' : 'cp437';
        }

        if ($encoding === 'utf8') {
            if (!$isUtf8) {
                throw new \RuntimeException("Le fichier n'est pas de l'UTF-8 valide (essayez --encoding=cp437 ou --encoding=auto).");
            }
        } else {
            $content = iconv(strtoupper($encoding), 'UTF-8//TRANSLIT', $content)
                ?: throw new \RuntimeException("Conversion depuis {$encoding} impossible.");
        }

        // Fins de ligne unifiées : plus de "\r" parasite en fin de dernière colonne
        return str_replace(["\r\n", "\r"], "\n", $content);
    }
}
