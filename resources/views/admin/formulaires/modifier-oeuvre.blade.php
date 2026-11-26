<x-app-layout>
    <!-- Modification d'un texte (oeuvre, contenu) -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="/admin">Administration BDFI</a> &rarr;
            <a href="/admin/formulaires">Formulaires</a> &rarr;
            Modification d'un texte (oeuvre)
        </h2>
    </x-slot>

    <x-admin.discovery-notice :allowed="auth()->user()->hasMemberRole()" />

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="border border-blue-800 rounded-lg p-1 m-1">
                <div class="hidden sm:block">
                    Choix du texte à modifier ou compléter (entrez un extrait de titre). Les textes se créent avec une publication ou un sommaire, pas ici.
                </div>
                <livewire:title-search-and-edit />
            </div>
        </div>
    </div>
</x-app-layout>
