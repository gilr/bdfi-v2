<x-app-layout>
    <!-- Ajout d'un évènement et/ou d'une occurrence d'évènement -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="/admin">Administration BDFI</a> &rarr;
            <a href="/admin/formulaires">Formulaires</a> &rarr;
            Ajout d'évènement
        </h2>
    </x-slot>

    <x-admin.discovery-notice :allowed="auth()->user()->hasMemberRole()" />

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="border border-blue-800 rounded-md p-1 m-1">
                Saisissez un extrait du nom de l'évènement (salon, festival, convention…). S'il est déjà référencé, sélectionnez-le :
                ses occurrences s'affichent, et vous pouvez en ajouter une nouvelle. Sinon, créez-le : vous saisirez alors l'évènement
                et sa première occurrence.
            </div>
            <div class="bg-white shadow-xl rounded p-4 m-1">
                <livewire:event-occurrence-add />
            </div>
        </div>
    </div>
</x-app-layout>
