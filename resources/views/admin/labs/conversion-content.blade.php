<x-app-layout>
    <!-- Page d'accueil administration (jetstream) -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="/admin">Administration BDFI</a> &rarr;
            <a href="/admin/labs">Labs - tests & outils</a> &rarr;
            Conversion de sommaire/contenu
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="border border-blue-800 rounded-lg p-1 m-1">
                <div class="hidden sm:block">
                    Transformation de sommaire au format v1. Utile pour l'alimentation BDFI v1 (et initialisation données V2).<br />
                    Pour le format CSV, copier-coller une sélection depuis excel qui englobe la ligne d'entête et en s'arrêtant à la colonne Traducteur. Le séparateur devient normalement une tabulation.
                </div>
                <livewire:converter-content />
            </div>
        </div>
    </div>
</x-app-layout>
