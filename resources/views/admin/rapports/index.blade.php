<x-app-layout>
    <!-- Page d'accueil administration (jetstream) -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="/admin">Administration BDFI</a> &rarr;
            <a href="/admin/rapports">Rapports</a>
        </h2>
    </x-slot>

    <div class="pt-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

                <div class="p-6">
                    <div class="p-2 text-2xl border-b border-yellow-800">
                        Rapports concernant les publications et textes
                    </div>
                    <div class="p-2">
                        <ul class="list-disc pl-4">
                            <li><x-admin.link lien='rapports/erreurs-variantes'>Erreurs sur les variantes de textes</x-admin.link> (rattachements incohérents)</li>
                        </ul>
                    </div>
                    <div class="p-2 text-2xl border-b border-yellow-800">
                        Rapports concernant les auteurs
                    </div>
                    <div class="p-2">
                        <ul class="list-disc pl-4">
                            <li><x-admin.link lien='rapports/dates-bizarres'>Dates de naissance bizarres</x-admin.link></li>
                            <li><x-admin.link lien='rapports/manque-date-naissance'>Dates de naissance inconnues</x-admin.link> (mais décès connu)</li>
                            <li><x-admin.link lien='rapports/manque-date-deces'>Dates de décès inconnues</x-admin.link> (et naissance connue)</li>
                            <li><x-admin.link lien='rapports/manque-nationalite'>Nationalités manquantes</x-admin.link></li>
                            <li><x-admin.link lien='rapports/erreur-nationalite'>Nationalités erronées</x-admin.link></li>
                            <li>Bios <x-admin.link lien='rapports/etat-biographies-0'>"vides"</x-admin.link> et
                            <x-admin.link lien='rapports/etat-biographies-1'>"en ébauche"</x-admin.link></li>
                            <li>Et bios en état <x-admin.link lien='rapports/etat-biographies-2'>"moyen"</x-admin.link>,
                            <x-admin.link lien='rapports/etat-biographies-3'>"acceptable"</x-admin.link> ou
                            <x-admin.link lien='rapports/etat-biographies-5'>"validées"</x-admin.link></li>
                            <li>Bios <x-admin.link lien='rapports/etat-biographies-4'>"à valider"</x-admin.link> et
                            <x-admin.link lien='rapports/etat-biographies-9'>"à revoir"</x-admin.link></li>
                            <li>(Fiches manquantes en base : non porté - pas forcément indispensable puisque inutile à terme)</li>
                        </ul>
                    </div>
                    <div class="p-2 text-2xl border-b border-yellow-800">
                        Rapports concernant les prix
                    </div>
                    <div class="p-2">
                        <ul class="list-disc pl-4">
                            <li><x-admin.link lien='rapports/prix-{{ date("Y") - 3 }}'>Manquants avant {{ date("Y") - 3 }} (ou prix arrété non indiqué)</x-admin.link></li>
                            <li><x-admin.link lien='rapports/prix-{{ date("Y") - 2 }}'>Manquants en {{ date("Y") - 2 }} ou avant</x-admin.link></li>
                            <li><x-admin.link lien='rapports/prix-{{ date("Y") - 1 }}'>Manquants en {{ date("Y") - 1 }} ou avant</x-admin.link></li>
                            <li><x-admin.link lien='rapports/prix-{{ date("Y") }}'>Manquants en {{ date("Y") }} ou avant</x-admin.link></li>
                        </ul>
                    </div>
                    <div class="p-2 text-2xl border-b border-yellow-800">
                        Les anniversaires d'auteurs
                    </div>
                    <div class="p-2">
                        <ul class="list-disc pl-4">
                            <li><x-admin.link lien='rapports/anniversaires-fb-jour'>Anniversaires du jour</x-admin.link></li>
                            <li><x-admin.link lien='rapports/anniversaires-fb-semaine'>Anniversaires  de la semaine</x-admin.link></li>
                            <li><x-admin.link lien='rapports/anniversaires-fb-mois'>Anniversaires du mois</x-admin.link></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
