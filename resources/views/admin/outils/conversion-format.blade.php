<x-app-layout>
    <!-- Page d'accueil administration (jetstream) -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="/admin">Administration BDFI</a> &rarr;
            <a href="/admin/outils">Outils</a> &rarr;
            Conversion de formats
        </h2>
    </x-slot>

    <x-admin.discovery-notice :allowed="auth()->user()->hasProponentRole()" />

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="border border-blue-800 rounded-lg p-1 m-1">
                <div class="hidden sm:block">
                    Transformation de format, applicable à des parties de fichier collection. Voir les avertissements plus bas.
                </div>
                <livewire:converter-format />
                <div class="hidden sm:block">
                    <b>Avertissements et restrictions :</b>
                     <br />- Formats disponibles en entrée : COL, CSV, JSON. Encodage du fichier choisi explicitement (auto, UTF-8, CP437, CP1252) ; une saisie dans la zone de texte est toujours considérée comme de l'UTF-8.
                     <br />- Formats disponibles en sortie : seul JSON est complet pour l'instant. COL et CSV sont au catalogue mais leur conversion en sortie (pivot → fichier) reste à écrire ; la conversion est refusée avec un message explicite si vous la demandez.
                     <br />- BBCODE et EXCEL sont prévus mais non implémentés, ni en entrée ni en sortie.
                     <br />- Un fichier CSV (ou une saisie CSV) doit contenir la ligne d'entête, avec ou sans les 2 colonnes prix ('PRIX (Award)' et 'AN_PRIX').
                     <br />- La transformation peut être parfois un peu longuette
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
