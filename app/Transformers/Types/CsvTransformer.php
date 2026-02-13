<?php

/*

Bassai :
 juste avant le 1-2 puis 1-2-3 : bras devant horizontal niveau plexus, autre main sous main
 1-2-3 : corps de 3/4
 ramener deux mains pile au moment du yoko barai
 positions kiba

*/

namespace App\Transformers\Types;

use App\Transformers\FormatTransformer;
use Illuminate\Http\UploadedFile;

class CsvTransformer implements FormatTransformer
{
    /**
     *   Fournit la ligne d'entête EXCEL
    **/
    protected function getExpectedHeader(): array
    {
        return [
            'Sexe', 'PRENOM', 'NOM', 'TITRE', 'Série', 'N° Série', 'TITRE_ORIGINAL', 'GENRE', 'RNO', 'A/J', 'Format', 'Dim./Durée', 'Pages', 'Imag/Blanche/Auto', 'EDITEUR', 'COLLECTION', 'NR', 'tri', 'nom_img', 'O', 'AN_COPY', 'Mois Paru', 'AN_PARU', 'O/R', 'Type', 'DL', 'AI', 'Imprimeur', 'S Tr', 'Traducteur', 'ISBN', 'Commentaires', 'Sexe Il', 'Illustr. / Narrat.'
        ];
    }
    protected function getExpectedHeaderBis(): array
    {
        return [
            'Sexe', 'PRENOM', 'NOM', 'TITRE', 'Série', 'N° Série', 'TITRE_ORIGINAL', 'GENRE', 'RNO', 'A/J', 'Format', 'Dim./Durée', 'Pages', 'Imag/Blanche/Auto', 'EDITEUR', 'COLLECTION', 'NR', 'tri', 'S', 'O', 'AN_COPY', 'Mois Paru', 'AN_PARU', 'O/R', 'Type', 'DL', 'AI', 'Imprimeur', 'S Tr', 'Traducteur', 'ISBN', 'Commentaires', 'Sexe Il', 'Illustr. / Narrat.'
        ];
    }
    /**
     *   Fournit la seconde ligne d'entête EXCEL admise, avec les deux colonnes prix
    **/
    protected function getExpectedHeader2(): array
    {
        return [
            'Sexe', 'PRENOM', 'NOM', 'TITRE', 'Série', 'N° Série', 'TITRE_ORIGINAL', 'GENRE', 'RNO', 'A/J', 'Format', 'Dim./Durée', 'Pages', 'Imag/Blanche/Auto', 'EDITEUR', 'COLLECTION', 'NR', 'tri', 'nom_img', 'O', 'AN_COPY', 'Mois Paru', 'AN_PARU', 'O/R', 'Type', 'DL', 'AI', 'Imprimeur', 'PRIX (Award)', 'AN_PRIX', 'S Tr', 'Traducteur', 'ISBN', 'Commentaires', 'Sexe Il', 'Illustr. / Narrat.'
        ];
    }
    protected function getExpectedHeader2Bis(): array
    {
        return [
            'Sexe', 'PRENOM', 'NOM', 'TITRE', 'Série', 'N° Série', 'TITRE_ORIGINAL', 'GENRE', 'RNO', 'A/J', 'Format', 'Dim./Durée', 'Pages', 'Imag/Blanche/Auto', 'EDITEUR', 'COLLECTION', 'NR', 'tri', 'S', 'O', 'AN_COPY', 'Mois Paru', 'AN_PARU', 'O/R', 'Type', 'DL', 'AI', 'Imprimeur', 'PRIX (Award)', 'AN_PRIX', 'S Tr', 'Traducteur', 'ISBN', 'Commentaires', 'Sexe Il', 'Illustr. / Narrat.'
        ];
    }

    /**
     *   Fonction de passage immediat au EXCEL sans passer par le format pivot
     *   Pour le passage inverse, voir la classe ExcelTransformer
    **/
    public function toExcel(string $input): string
    {
        $expectedHeader = $this->getExpectedHeader();
        $expectedHeaderBis = $this->getExpectedHeaderBis();
        $expectedHeader2 = $this->getExpectedHeader2();
        $expectedHeader2Bis = $this->getExpectedHeader2Bis();

        $lines = explode("\n", trim($input));
        $headerLine = array_shift($lines);
        $actualHeader = str_getcsv($headerLine, ';');

        // Nettoyage des colonnes vides finales
        while (end($actualHeader) === '') {
            array_pop($actualHeader);
        }

        if (($actualHeader !== $expectedHeader) && ($actualHeader !== $expectedHeaderBis) &&
            ($actualHeader !== $expectedHeader2) && ($actualHeader !== $expectedHeader2Bis)) {
            throw new \Exception("En-tête CSV invalide : les colonnes ne correspondent pas à la structure attendue.");
        }

        $rows = [$actualHeader];
        foreach ($lines as $line) {
            if (trim($line) === '') continue;

            $rowValues = str_getcsv($line, ';');
            $rowValues = array_slice($rowValues, 0, count($actualHeader));
            $rowValues = array_pad($rowValues, count($actualHeader), '');

            $rows[] = $rowValues;
        }

        // Utilise Laravel Excel pour générer un CSV Excel-compatible
        $tempFile = tempnam(sys_get_temp_dir(), 'csv2xlsx_') . '.xlsx';
        \Maatwebsite\Excel\Facades\Excel::store(new class($rows) implements \Maatwebsite\Excel\Concerns\FromArray {
            public function __construct(private array $rows) {}
            public function array(): array { return $this->rows; }
        }, $tempFile, null, \Maatwebsite\Excel\Excel::XLSX);

        return file_get_contents($tempFile);
    }

    /**
     *   Transformation vers le format pivot
    **/
    public function toArray(array|string|UploadedFile $input): array
    {
        if (is_array($input)) return $input;

        $expectedHeader = $this->getExpectedHeader();
        $expectedHeaderBis = $this->getExpectedHeaderBis();
        $expectedHeader2 = $this->getExpectedHeader2();
        $expectedHeader2Bis = $this->getExpectedHeader2Bis();

        $lines = explode("\n", trim($input));
        $headerLine = array_shift($lines);
        $actualHeader = str_getcsv($headerLine, ';');
        // Supprime les colonnes vides en fin d'en-tête
        while (end($actualHeader) === '') {
            array_pop($actualHeader);
        }

        //dd($actualHeader);

        if (($actualHeader !== $expectedHeader) && ($actualHeader !== $expectedHeaderBis) &&
            ($actualHeader !== $expectedHeader2) && ($actualHeader !== $expectedHeader2Bis)) {
            throw new \Exception("En-tête CSV invalide : les colonnes ne correspondent pas à la structure attendue : [ $headerLine ].");
        }

        $data = [];
        foreach ($lines as $line) {
            if (trim($line) === '') continue;

            $rowValues = str_getcsv($line, ';');

            // Ajuste le nombre de colonnes à celui de l'en-tête
            $rowValues = array_slice($rowValues, 0, count($actualHeader)); // tronque si trop long
            $rowValues = array_pad($rowValues, count($actualHeader), '');  // complète si trop court

            $row = array_combine($actualHeader, $rowValues);
            $data[] = $this->map($row);
        }

        return $data;
    }

    /**
     *   Transformation depuis le format pivot
    **/
    public function fromArray(array $data): string|array
    {
        if (empty($data)) return '';

        $header = $this->getExpectedHeader();

        $mapped = array_map(fn($row) => $this->reverseMap($row), $data);

        $csv = [implode(';', $header)];

        foreach ($mapped as $row) {
            $ordered = array_map(fn($key) => $row[$key] ?? '', $header);
            $csv[] = implode(';', array_map(fn($value) => '"' . str_replace('"', '""', $value) . '"', $ordered));
        }

        return implode("\n", $csv);
    }

    /**
     *   Fonction de mapping entre les données du CSV et les données pivot
    **/
    public function map(array $csvRow): array
    {
        return [
            'titre' => trim($csvRow['TITRE']),

            'auteurs' => [
                "0" => [
                    'auteur_prenom' => trim($csvRow['PRENOM']),
                    'auteur_nom' => trim($csvRow['NOM']),
                ]
            ],
            // TODO : gérer les auteurs multiples

            'serie' => trim($csvRow['Série']),
            'num_serie' => trim($csvRow['N° Série']),
            'mois_parution' => $this->getMoisParution($csvRow['Mois Paru']),
            'annee_parution' => trim($csvRow['AN_PARU']),
            'dl' => $this->getDLAI(trim($csvRow['DL'])),
            'ai' => $this->getDLAI(trim($csvRow['AI'])),
            'imprimeur' => trim($csvRow['Imprimeur']),

            // TODO : attention au format avec ISBN VN
            'isbn' => $csvRow['ISBN'],

            // éditeur & collection
            'editeur' => $csvRow['EDITEUR'],
            'collection' => $csvRow['COLLECTION'],
            'no_collection' => $csvRow['NR'],

            'est_genre' => $this->getEstGenre($csvRow['GENRE']),
            'genre' => $this->getGenre($csvRow['GENRE']),
            'dimensions' => trim($csvRow['Dim./Durée']),
            'approximate_pages' => trim($csvRow['Pages']),
            'type_ouvrage' => $this->getTypeOuvrage($csvRow['RNO']),

            'format' => $this->getFormat($csvRow['Format']),
            'support' => $this->getSupport($csvRow['Format']),
            'reliure' => $this->getReliure($csvRow['Format']),
            'jaquette' => $this->getJaquette($csvRow['Format']),
            'rabat' => $this->getRabat($csvRow['Format']),

            // type_tirage (inedit, premiere, retirage, reedition) en fonction de 'O/R' et de 'S' (scan)... disparu du fichier ?
            'type_edition' => $this->getTypeEdition($csvRow['O/R'], $csvRow['nom_img'] ?? $csvRow['S']),

            // images
            'img_fichier' => $csvRow['nom_img'] ?? $csvRow['S'],
            // TODO : réduire img_couv, et compléter avec les images de bandeau & back si besoin
            'img_couv' => $csvRow['nom_img'] ?? $csvRow['S'],

            'est_verifie' => $this->getVerification($csvRow['O'], 'est_verifie'),
            'verifie_par' => $this->getVerification($csvRow['O'], 'verifie_par'),
            // audience & age
            'audience' => $this->getAudienceTarget($csvRow['A/J']),

            'illustrateur_couv' => $this->getIllustrateurs($csvRow['Illustr. / Narrat.'], 'couv'),
            'illustrateurs' => $this->getIllustrateurs($csvRow['Illustr. / Narrat.'], 'interieur'),

            'sommaire' => [
                "0" => [
                    'titre' => trim($csvRow['TITRE']),
                    'auteurs' => [
                        "0" => [
                            'auteur_prenom' => trim($csvRow['PRENOM']),
                            'auteur_nom' => trim($csvRow['NOM']),
                        ]
                    ],
                    // TODO : gérer les auteurs multiples
                    'titre_vo' => $this->getTitreVO(trim($csvRow['TITRE_ORIGINAL'])),
                    'copyright' => trim($csvRow['AN_COPY']),
                    'serie' => trim($csvRow['Série']),
                    // TODO : extraire le titre VO de la série
                    'num_serie' => trim($csvRow['N° Série']),
                    'type_texte' => $this->getTypeTexte(trim($csvRow['RNO'])),
                    'traducteurs' => $this->getTraducteurs(trim($csvRow['Traducteur'])),
                ]
            ]

        ];
    }

    protected function getTraducteurs(string $value): string
    {
        return ($value === "_" ? "" : $value);
    }
    protected function getTitreVO(string $value): string
    {
        return ($value === "_" ? "" : $value);
    }

    protected function getIllustrateurs(string $value, string $type): string
    {
        // split de la chaine CM selon '+' (couv / interieur)
        list($couv, $interieur) = array_pad(explode(" + ", $value), 2, '');

        // récup de la valeur demandée
        return ($type === 'couv' ? $couv : $interieur);
    }

    protected function getVerification(string $value, string $type): string
    {
        if ($type === 'est_verifie')
        {
            return ($value === 'X' ? "oui" : "non");
        }
        else {
            return ($value === 'X' ? "CHRISTIAN" : "");
        }
    }

    protected function getAudienceTarget(string $value): string
    {
        return match (trim(strtolower($value))) {
            'a' => 'adulte-only',
            'j' => 'jeunesse',
            'y' => 'YA',
            default => 'inconnu',
        };
    }

    protected function getMoisParution(string $value): string
    {
        return match (trim(strtolower($value))) {
            '1' => '01',
            '01' => '01',
            '2' => '02',
            '02' => '02',
            '3' => '03',
            '03' => '03',
            '4' => '04',
            '04' => '04',
            '5' => '05',
            '05' => '05',
            '6' => '06',
            '06' => '06',
            '7' => '07',
            '07' => '07',
            '8' => '08',
            '08' => '08',
            '9' => '09',
            '09' => '09',
            '10' => '19',
            '11' => '11',
            '12' => '12',
            '1T' => 'T1',
            '2t' => 'T2',
            '3t' => 'T3',
            '4t' => 'T4',
            default => 'xx',
        };
    }
    protected function getDLAI(string $value): string
    {
        // remplacer 1er trimestre par T1, etc...
        $result = $value;
/*        $result = str_replace('1er trimestre', 'T1', $result);
        $result = str_replace('2ième trimestre', 'T2', $result);
        $result = str_replace('3ième trimestre', 'T3', $result);
        $result = str_replace('4ième trimestre', 'T4', $result); */
        return $result;
    }
    protected function getEstGenre(string $value): string
    {
        return match (trim(strtolower($value))) {
            'sf' => 'oui',
            'fy' => 'oui',
            'fant' => 'oui',
            'hyb' => 'oui',
            'hg' => 'non',
            default => '?',
        };
    }
    protected function getGenre(string $value): string
    {
        return match (trim(strtolower($value))) {
            'sf' => 'sf',
            'fy' => 'fantasy',
            'fant' => 'fantastique',
            'hyb' => 'imaginaire',
            'hg' => 'autre',
            default => 'autre',
        };
    }
    protected function getSupport(string $format): string
    {
        if ($format === "N") {
            return 'numerique';
        }
        else if ($format === "A") {
            return 'audio';
        }
        else if ($format === "C") {
            return 'coffret';
        }
        else {
            return 'papier';
        }
    }
    protected function getFormat(string $format): string
    {
        if (($format === "P") || ($format === "PR") || ($format === "RP")) {
            return 'poche';
        }
        else if (($format === "Pj") || ($format === "Pr")) {
            return 'poche';
        }
        else if (($format === "GF") || ($format === "GFR") || ($format === "RGF")) {
            return 'gf';
        }
        else if (($format === "GFj") || ($format === "GFr")) {
            return 'gf';
        }
        else if (($format === "MF") || ($format === "MFR") || ($format === "RMF")) {
            return 'mf';
        }
        else if (($format === "MFj") || ($format === "MFr")) {
            return 'mf';
        }
        else if ($format === "N") {
            return 'n-a';
        }
        else if ($format === "A") {
            return 'n-a';
        }
        else if ($format === "C") {
            return 'autre';
        }
        else {
            return 'inconnu';
        }

    }
    protected function getReliure(string $format): string
    {
        if (($format === "PR") || ($format === "RP") ||
            ($format === "MFR") || ($format === "RMF") ||
            ($format === "GFR") || ($format === "RGF")) {
            return "oui";
        }
        return "non";
    }
    protected function getJaquette(string $format): string
    {
        if (($format === "Pj") ||
            ($format === "MFj") ||
            ($format === "GFj")) {
            return "oui";
        }
        return "non";
    }
    protected function getRabat(string $format): string
    {
        if (($format === "Pr") ||
            ($format === "MFr") ||
            ($format === "GFr")) {
            return "oui";
        }
        return "non";
    }
    protected function getTypeEdition(string $or, string $img): string
    {
        if ($or === "I") {
            return 'inedit';
        }
        else if ($or === "O") {
            return 'premiere_collection';
        }
        else if ($or === "R") {
            if ($img === "_") {
                return 'retirage_collection';
            }
            else {
                return 'reedition_collection';
            }
        }
        else {
            // TODO : logger erreur sur la valeur O/R
            return 'inedit';
        }
        return $result;
    }
    protected function getEstNovellisation(string $value): string
    {
        return match (trim(strtolower($value))) {
            'novellisation' => 'oui',
            default => 'non',
        };
    }
    protected function getTypeOuvrage(string $value): string
    {
        // Roman, Recueil, Anthologie, Omnibus, Novella, Nouvelle, Novellisation, Essai, Coffret, Livre-Jeu, Art-book, Théatre et Revues
        return match (trim(strtolower($value))) {
            'roman' => 'roman',
            'recueil' => 'compilation',
            'anthologie' => 'compilation',
            'omnibus' => 'omnibus',
            'novella' => 'fiction',
            'nouvelle' => 'fiction',
            'novellisation' => 'roman',
            'essai' => 'non-fiction',
            'coffret' => 'compilation',
            'livre-jeu' => 'non-fiction',
            'art-book' => 'non-fiction',
            'theatre' => 'fiction',
            'revue' => 'periodique',
            default => '?',
        };
    }
    protected function getTypeTexte(string $value): string
    {
        // Roman, Recueil, Anthologie, Omnibus, Novella, Nouvelle, Novellisation, Essai, Coffret, Livre-Jeu, Art-book, Théatre et Revues
        return match (trim(strtolower($value))) {
            'roman' => 'novel',
            'recueil' => 'collection',
            'anthologie' => 'anthologie',
            'omnibus' => 'omnibus',
            'novella' => 'novella',
            'nouvelle' => 'shortstory',
            'novellisation' => 'roman',
            'essai' => 'essai',
            'coffret' => 'inconnu',
            'livre-jeu' => 'gamebook',
            'art-book' => 'inconnu',
            'theatre' => 'theatre',
            'revue' => 'magazine',
            default => 'inconnu',
        };
    }

    /* exemples :
        protected function boolFromText(string $value): bool
        {
            return in_array(strtolower($value), ['oui', 'yes', 'true', '1']);
        }
    */


    public function reverseMap(array $pivotRow): array
    {
        return [
            'Sexe' => '',
            'PRENOM' => '',
            'NOM' => '',
            'TITRE' => 'Non réalisé !!',
            'Série' => '',
            'N° Série' => '',
            'TITRE_ORIGINAL' => '',
            'GENRE' => '',
            'RNO' => '',
            'A/J' => '',
            'Format' => '',
            'Dim./Durée' => '',
            'Pages' => '',
            'Imag/Blanche/Auto' => '',
            'EDITEUR' => '',
            'COLLECTION' => '',
            'NR' => '',
            'tri' => '',
            'nom_img' => '',
            'O' => '',
            'AN_COPY' => '',
            'Mois Paru' => '',
            'AN_PARU' => '',
            'O/R' => '',
            'Type' => '',
            'DL' => '',
            'AI' => '',
            'Imprimeur' => '',
            'S Tr' => '',
            'Traducteur' => '',
            'ISBN' => '',
            'Commentaires' => '',
            'Sexe Il' => '',
            'Illustr. / Narrat.' => '',
        ];
    }

}



/*

--- Format CSV

    $csvRow['Sexe']
    $csvRow['PRENOM']
    $csvRow['NOM']
    $csvRow['TITRE']
    $csvRow['Série']
    $csvRow['N° Série']
    $csvRow['TITRE_ORIGINAL']
    $csvRow['GENRE']                #-- SF, Fy, Fant, Hyb, et HG
    $csvRow['RNO']                  #-- Roman, Recueil, Anthologie, Omnibus, Novella, Nouvelle, Novellisation, Essai, Coffret, Livre-Jeu, Art-book, Théatre et Revues
    $csvRow['A/J']                  #-- A=adulte, J=jeunesse < 13a, Y=YA > 13a
    $csvRow['Format']               #-- GF, MF, P - GFR, MFR, PR =si reliés - A=audio, N=numérique, C=Coffret
    $csvRow['Dim./Durée']           #-- = durée si audio
    $csvRow['Pages']                #-- Si jai le bouquin : cest le nombre de pages intéressantes du texte, y compris postface et table des matière, mais non compris liste des titres de la collection, donc pas forcément la dernière page numérotée
    $csvRow['Imag/Blanche/Auto']    #-- Auto = auto-éditions = A, Imaginaire=I et comme je suis très cohérent, Blanche = G
    $csvRow['EDITEUR']
    $csvRow['COLLECTION']
    $csvRow['NR']
    $csvRow['tri']                  #-- peut être au format 2021-xx
    $csvRow['nom_img']
    $csvRow['O']                    #-- S=Scan : X si possédé par CM, "_" si identique édition précédente
    $csvRow['AN_COPY']
    $csvRow['Mois Paru']
    $csvRow['AN_PARU']
    $csvRow['O/R']                  #-- I= inédit, O=première édition dans collection, R=réimpression dans la collection   => retirage si cm_S = "_" et O/R = "R"
    $csvRow['Type']                 #-- "type" en cas de changement de maquette  "--" pour premier titre de la collection, "//" pour dernier
    $csvRow['DL']
    $csvRow['AI']                   #-- "n.i." = non indiqué
    $csvRow['Imprimeur']
    $csvRow['S Tr']
    $csvRow['Traducteur']           #-- "A & B" si collab, "A + B" si révision par B
    $csvRow['ISBN']
    $csvRow['Commentaires']
    $csvRow['Sexe Il']
    $csvRow['Illustr. / Narrat.']   #-- "A & B" si collab, "A + B" si illustations intérieures (B)

--- Format JSON

    'titre' => $titre,
    'auteur_prenom' => $prenom,
    'auteur_nom' => $nom,
    'annee_parution'] = $year;
    'hors_genres' => $horsGenres,
    'serie' => $serie,
    'parution' => $year,
    'isbn' => $isbn,
    'illustrateur_couv' => $illustrateur,
    'illustrateurs' => $illustrateur,
    'sommaire' => [
        [
            'hors_genres' => $horsGenres,
            'auteur_prenom' => $prenom,
            'auteur_nom' => $nom,
            'titre' => $titre,
            'serie' => $serie,
            'copyright' => $copyright,
        ]



--- Format Base ouvrage

    $table->string('name', 128);
    $table->string('slug', 128)->nullable();

    $table->string('status')->default(PublicationStatus::PUBLIE->value); // PUBLIE, ANNONCE, PROPOSE, ABANDONNE

    $table->string('cycle', 128)->nullable();
    $table->string('cyclenum', 10)->nullable();

    $table->unsignedInteger('publisher_id')->nullable()->default(NULL);
    $table->foreign('publisher_id')
        ->references('id')
        ->on('publishers')
        ->onDelete('restrict');
    $table->string('publisher_name', 128)->nullable(); // Surcharge pour si renommage

    $table->boolean('is_visible')->default(true);

    $table->string('isbn', 18)->nullable();
    $table->string('cover', 256)->nullable();
    $table->string('illustrators', 512)->nullable();
    $table->text('information')->nullable();
    $table->text('private')->nullable();

    $table->string('cover_front', 64)->nullable();
    $table->string('cover_back', 64)->nullable();
    $table->string('cover_spine', 64)->nullable();
    $table->string('withband_front', 64)->nullable();
    $table->string('withband_back', 64)->nullable();
    $table->string('withband_spine', 64)->nullable();
    $table->string('dustjacket_front', 64)->nullable();
    $table->string('dustjacket_back', 64)->nullable();
    $table->string('dustjacket_spine', 64)->nullable();

    $table->boolean('is_hardcover')->default(false);
    $table->boolean('has_dustjacket')->default(false);
    $table->boolean('has_coverflaps')->default(false);

    $table->boolean('is_verified');
    $table->string('verified_by', 256)->nullable();
    $table->string('dl', 10)->nullable();
    $table->string('ai', 10)->nullable();
    $table->string('edition', 64)->nullable();
    $table->string('dimensions', 10)->nullable();
    $table->string('thickness', 4)->nullable();
    $table->string('printer', 128)->nullable();
    $table->string('printed_price', 32)->nullable();
    $table->string('pagination', 32)->nullable();
    $table->string('pages_dpi')->nullable();
    $table->string('pages_dpu')->nullable();

    // Données indicatives :
    $table->string('approximate_pages')->nullable();
    $table->string('approximate_parution', 10)->nullable();
    $table->string('approximate_price', 32)->nullable();

    $table->string('support')->default(PublicationSupport::PAPIER->value); // papier, numerique, audio, autre
    $table->string('format')->default(PublicationFormat::INCONNU->value); // Poche, GF, MF, autre, n-a, inconnu
    $table->string('type'); // Enum PublicationContent : fiction, assemblage, omnibus, revue, non-fiction

    // Genre - thésaurus : à revoir
    $table->string('is_genre'); // Enum GenreAppartenance : yes, partial, no
    $table->string('genre_stat')->default(GenreStat::INCONNU->value); // sf, fantasy, fantastique, hybride, autre, mainstream
    $table->string('target_audience')->default(AudienceTarget::INCONNU->value);;
    $table->string('target_age')->nullable();

*/
