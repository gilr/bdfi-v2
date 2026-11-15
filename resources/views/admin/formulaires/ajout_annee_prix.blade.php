<x-app-layout>
    <!-- Saisie rapide des gagnants d'une année de prix -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="/admin">Administration BDFI</a> &rarr;
            <a href="/admin/formulaires">Formulaires</a> &rarr;
            Ajout gagnants d'une année de prix
        </h2>
    </x-slot>

    <x-admin.discovery-notice :allowed="auth()->user()->hasMemberRole()" />

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="border border-blue-800 rounded-md p-1 m-1">
                Choisissez un prix et une année : une ligne apparaît pour chacune de ses catégories.
                Si la page a déjà des données pour ce prix et cette année, elles sont reprises (vous pouvez les corriger).
                Remplissez "Attribué à" avec le ou les nom(s) de la ou des personne(s) lauréate(s). Si plusieurs, indiquez "Auteur 1 et Auteur 2" ou "Auteur 1, Auteur 2 et Auteur3". Laissez vide si la catégorié est non décernée cette année-là. Les liens auteurs (si en base) doivent suivrent la même position eaxctement que dans le l'ordre de "attribué pour".
            </div>

            @if ($errors->any())
                <div class="p-2">
                    <div class="p-2 text-xl border-b border-red-800 bg-red-100">
                        Erreur lors de la tentative d'enregistrement
                    </div>
                    <div class="p-2">
                        <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if (!empty($status))
                <div class="p-2">
                    <div class="p-2 text-xl border-b border-green-800 bg-green-100">
                        Enregistré : {{ $awardName }} {{ $year }} ({{ $savedCount }} catégorie(s))
                    </div>
                    <div class="p-2">
                        Page publique : <x-front.lien-standard link="/prix/annee/{{ $year }}">Prix {{ $year }}</x-front.lien-standard>
                        — fiches à compléter (informations, vérification…) dans
                        <a class="text-red-700" href="/filament/{{ $filament }}" target="_blank">la gestion des tables</a> (nouvel onglet).
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="/admin/formulaires/ajout-annee-prix" class="bg-white shadow-xl rounded p-4 m-1">
                @csrf

                <livewire:award-winners-year :old-award-id="$oldAwardId" :old-year="$oldYear" />
            </form>
        </div>
    </div>
</x-app-layout>
