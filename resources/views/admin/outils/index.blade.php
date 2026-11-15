<x-app-layout>
    <!-- Page d'accueil administration (jetstream) -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="/admin">Administration BDFI</a> &rarr;
            <a href="/admin/outils">Outils</a>
        </h2>
    </x-slot>

    <x-admin.discovery-notice :allowed="auth()->user()->hasProponentRole()" />

    <div class="pt-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

                <div class="p-6">
                    {{-- Imports : visibles de l'invité à l'admin, mais liens actifs pour le sysadmin seul (pour l'instant) --}}
                    <div class="p-2 text-2xl border-b border-yellow-800">
                        Import de publications
                    </div>
                    <div class="p-2">
                        <ul class="list-disc pl-4">
                            @if (auth()->user()?->hasSysAdminRole())
                                <li><x-admin.link lien='outils/import-analyse'>Analyse d'un fichier d'import (COL, CSV, JSON)</x-admin.link></li>
                                <li><x-admin.link lien='outils/import'>Importer un fichier d'import</x-admin.link> (simulation ou import réel)</li>
                            @else
                                <li><span class="text-gray-400 cursor-not-allowed" title="Réservé à l'administrateur système">Analyse d'un fichier d'import (COL, CSV, JSON)</span></li>
                                <li><span class="text-gray-400 cursor-not-allowed" title="Réservé à l'administrateur système">Importer un fichier d'import</span> (simulation ou import réel)</li>
                                <li class="list-none text-sm text-gray-500 italic">Réservé pour l'instant à l'administrateur système.</li>
                            @endif
                        </ul>
                    </div>
                    <div class="p-2 text-2xl border-b border-yellow-800">
                        Transformation entre formats
                    </div>
                    <div class="p-2">
                        <ul class="list-disc pl-4">
                            <li><x-admin.link lien='outils/conversion-format'>Conversion de format</x-admin.link></li>
                        </ul>
                    </div>
                    <div class="p-2">
                        <ul class="list-disc pl-4">
                            <li><x-admin.link lien='outils/conversion-content'>Conversion sommaire</x-admin.link></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
