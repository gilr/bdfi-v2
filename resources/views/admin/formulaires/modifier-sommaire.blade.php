<x-app-layout>
    <!-- Sommaire d'une publication -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="/admin">Administration BDFI</a> &rarr;
            <a href="/admin/formulaires">Formulaires</a> &rarr;
            Sommaire d'une publication
        </h2>
    </x-slot>

    <x-admin.discovery-notice :allowed="auth()->user()->hasMemberRole()" />

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="border border-blue-800 rounded-lg p-1 m-1">
                <div class="hidden sm:block">
                    Choix de la publication dont il faut ajouter, compléter ou corriger le sommaire (entrez un extrait de titre ou d'ISBN).
                    Le contenu principal (équivalent du titre de l'ouvrage) est toujours à la racine ; les autres textes sont rattachés dessous.
                </div>
                <livewire:publication-summary-editor />
            </div>
        </div>
    </div>
</x-app-layout>
