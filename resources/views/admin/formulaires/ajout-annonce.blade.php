<x-app-layout>
    <!-- Ajout d'une annonce ou d'un remerciement -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="/admin">Administration BDFI</a> &rarr;
            <a href="/admin/formulaires">Formulaires</a> &rarr;
            Ajout d'annonce ou de remerciement
        </h2>
    </x-slot>

    <x-admin.discovery-notice :allowed="auth()->user()->hasMemberRole()" />

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="border border-blue-800 rounded-md p-1 m-1">
                Choisissez le type d'information : l'aide du champ « Titre / Sujet » s'adapte au type.
                Les dernières saisies du même type s'affichent sous le formulaire, pour repère. Après l'enregistrement,
                le type et la date sont conservés, pour enchaîner plusieurs saisies.
            </div>
            <div class="bg-white shadow-xl rounded p-4 m-1">
                <livewire:announcement-add />
            </div>
        </div>
    </div>
</x-app-layout>
