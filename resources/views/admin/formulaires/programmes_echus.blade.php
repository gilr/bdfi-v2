<x-app-layout>
    <!-- Page d'accueil administration (jetstream) -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="/admin">Administration BDFI</a> &rarr;
            <a href="/admin/formulaires">Formulaires</a> &rarr;
            Vérification des programmations échues
        </h2>
    </x-slot>

    <div class="pb-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4">

                <div class="border border-yellow-800 bg-yellow-100 rounded-lg p-2">
                    <p>Pour chaque ouvrage annoncé dont la date est atteinte, vous pouvez directement :</p>
                    <ul class="list-disc pl-6">
                        <li><span class="font-semibold">Cliquer sur 'Paru'</span> : l'ouvrage est bien paru, la date indiquée est conservée.</li>
                        <li><span class="font-semibold">Changer la date</span> (AAAA-MM-JJ, AAAA-MM-00, AAAA-00-00 ou AAAA-Tn-00) : une date <em>précise et passée</em> suffit, l'ouvrage est alors marqué paru ; une date à venir repousse simplement la parution (l'ouvrage quitte cette liste).</li>
                        <li><span class="font-semibold">Cliquer sur 'Jamais paru'</span> : l'éditeur a abandonné le projet.</li>
                    </ul>
                    <p>Le lien « Fiche » (qui s'ouvre dans un autre onglet) permet de compléter le reste : informations, collection, auteur(s), contenu (créer au préalable les auteurs ou textes inexistants).</p>
                </div>

                <div class="p-2 text-xl">Liste des publications annoncées et dates dépassées :</div>

                <livewire:scheduled-publications mode="expired" />

            </div>
        </div>
    </div>
</x-app-layout>
