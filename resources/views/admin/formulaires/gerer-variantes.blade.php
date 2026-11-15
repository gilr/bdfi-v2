<x-app-layout>
    <!-- Gestion des variantes de textes (parent + variantes) -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="/admin">Administration BDFI</a> &rarr;
            <a href="/admin/formulaires">Formulaires</a> &rarr;
            Variantes de textes
        </h2>
    </x-slot>

    <x-admin.discovery-notice :allowed="auth()->user()->hasMemberRole()" />

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="border border-blue-800 rounded-md p-1 m-1">
                Choisissez un texte quelconque : son ensemble s'affiche (le parent, « premier » ou « virtuel », et toutes ses variantes).
                Vous pouvez y rattacher une variante, en retirer une, changer un type, ou désigner une variante comme nouveau parent
                (toutes les autres lui sont alors rattachées). Chaque action est enregistrée immédiatement.
            </div>
            <div class="bg-white shadow-xl rounded p-4 m-1">
                <livewire:title-variants-manager />
            </div>
        </div>
    </div>
</x-app-layout>
