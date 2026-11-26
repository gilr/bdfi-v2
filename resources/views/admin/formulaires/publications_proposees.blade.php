<x-app-layout>
    <!-- Étude des propositions d'ajout d'ouvrage -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="/admin">Administration BDFI</a> &rarr;
            <a href="/admin/formulaires">Formulaires</a> &rarr;
            Propositions d'ajout d'ouvrages
        </h2>
    </x-slot>

    <div class="pb-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4">

                <div class="border border-yellow-800 bg-yellow-100 rounded-lg p-2 mb-2">
                    <p>Les propositions des contributeurs sont stockées à part : rien n'est dans la base des ouvrages avant l'acceptation. « Étudier » affiche la proposition et l'analyse de l'import (doublon, éditeur et collection trouvés ou non, auteurs et textes à créer).</p>
                    <ul class="list-disc pl-6">
                        <li><span class="font-semibold">Accepter</span> crée l'ouvrage (et ses auteurs et textes manquants) : l'éditeur et la collection doivent exister, à créer au préalable si besoin. Complétez ensuite l'ouvrage avec les formulaires de modification et de vérification.</li>
                        <li><span class="font-semibold">Refuser</span> demande un motif, visible du proposant.</li>
                    </ul>
                </div>

                <livewire:proposal-review />

            </div>
        </div>
    </div>
</x-app-layout>
