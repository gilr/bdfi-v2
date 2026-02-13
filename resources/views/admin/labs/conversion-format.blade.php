<x-app-layout>
    <!-- Page d'accueil administration (jetstream) -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="/admin">Administration BDFI</a> &rarr;
            <a href="/admin/labs">Labs - tests & outils</a> &rarr;
            Conversion de formats
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="border border-blue-800 rounded-lg p-1 m-1">
                <div class="hidden sm:block">
                    Transformation de format, applicable à des parties de fichier collection. Voir les avertissements plus bas.
                </div>
                <livewire:converter-format />
                <div class="hidden sm:block">
                    <b>Avertissements et restrictions :</b>
                     <br />- La conversion depuis EXCEL (et aussi depuis le format v1 DOS) ne fonctionne évidemment que par import de fichier
                     <br />- Un fichier Excel ou CSV (ou une saisie CSV) doit contenir la ligne d'entête. Deux formats sont acceptés, avec ou sans les 2 colonnes prix ('PRIX (Award)' et 'AN_PRIX').
                     <br />- Le format 'BBCODE' (import ou export) ne gère que le format type éditeur "inanna", c'est à dire, des lignes [b]Année[/b], chacune suivie d'une liste d'ouvrages, suivie d'une liste de vignettes avec lien vers couverture.
                     <br />- Le format 'COL CP437' est un encodage DOS (CP437) à l'export, mais transformé en UTF-8 pour l'affichage.
                     <br />- La transformation peut être parfois un peu longuette
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
