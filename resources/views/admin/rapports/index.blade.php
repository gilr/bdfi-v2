<x-app-layout>
    <!-- Page d'accueil administration (jetstream) -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {!! __('Administration BDFI &rarr; Rapports') !!}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

                <div class="p-6">
                    <div class="p-2 text-2xl border-b border-yellow-800">
                        Rapports sur tables auteurs
                    </div>
                    <div class="p-2">
                        <ul class="list-disc pl-4">
                            <li><x-admin.link lien='rapports/dates-bizarres'>Dates de naissance bizarres</x-admin.link></li>
                            <li><x-admin.link lien='rapports/manque-date-naissance'>Date de naissance inconnue</x-admin.link> (mais décès connu)</li>
                            <li><x-admin.link lien='rapports/manque-date-deces'>Date de décès inconnue</x-admin.link> (et naissance connue)</li>
                            <li><x-admin.link lien='rapports/manque-nationalite'>Nationalité manquante</x-admin.link></li>
                            <li>Bios <x-admin.link lien='rapports/etat-biographies-0'>"vides"</x-admin.link> et
                            <x-admin.link lien='rapports/etat-biographies-1'>"en ébauche"</x-admin.link></li>
                            <li>Et bios en état <x-admin.link lien='rapports/etat-biographies-2'>"moyen"</x-admin.link>,
                            <x-admin.link lien='rapports/etat-biographies-3'>"acceptable"</x-admin.link> ou
                            <x-admin.link lien='rapports/etat-biographies-5'>"validées"</x-admin.link></li>
                            <li>Bios <x-admin.link lien='rapports/etat-biographies-4'>"à valider"</x-admin.link> et
                            <x-admin.link lien='rapports/etat-biographies-9'>"à revoir"</x-admin.link></li>
                            <li>(Fiches manquantes en base : non encore porté - pas forcément utile puisque plus besoin à terme)</li>
                        </ul>
                    </div>
                    <div class="p-2 text-2xl border-b border-yellow-800">
                        Rapports sur tables prix
                    </div>
                    <div class="p-2">
                        <ul class="list-disc pl-4">
                            <li><x-admin.link lien='rapports/prix-{{ date("Y") - 3 }}'>Manquants avant {{ date("Y") - 3 }} (ou prix arrété non indiqué)</x-admin.link></li>
                            <li><x-admin.link lien='rapports/prix-{{ date("Y") - 2 }}'>Manquants en {{ date("Y") - 2 }} ou avant</x-admin.link></li>
                            <li><x-admin.link lien='rapports/prix-{{ date("Y") - 1 }}'>Manquants en {{ date("Y") - 1 }} ou avant</x-admin.link></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
