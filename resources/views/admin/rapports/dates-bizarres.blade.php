<x-app-layout>
    <!-- Page d'accueil administration (jetstream) -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {!! __('Administration BDFI &rarr; Rapports &rarr; Dates étranges') !!}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4">

                <div class="border border-yellow-800 bg-yellow-100 rounded-lg p-2">
                    <p>Au final, il ne devrait rester que les quelques "circa", éventuellement négatives.</p>
                    <p>Rappel du format : AAAA-MM-JJ, avec :</p>
                    <ul class="list-disc pl-6">
                        <li>AAAA : année; de -999 à 2015; 0000 si inconnu; -0120 autorisé (120 Avant JC)</li>
                        <li>MM : mois; de 01 à 12; 00 si inconnu</li>
                        <li>JJ : jour; de 01 à 31; 00 si inconnu</li>
                        <li>Pour les années approximatives, "MM-JJ" est remplacé par "circa"; exemple = 1100-circa</li>
                        <li>Différence entre -00-00 et -circa : circa est à réserver aux dates suffisemment anciennes, dont on ne connaîtra jamais la précision</li>
                    </ul>
                </div>

                <div class="p-2 text-xl">Dates de naissance potentiellement étranges...</div>

                <table class="table-auto border-collapse border border-blue-800 w-full">
                    <thead>
                        <tr class="bg-blue-50">
                            <th class="p-1">Nom</th>
                            <th class="p-1">Naissance</th>
                            <th class="p-1">Décès</th>
                            <th class="p-1">Fiche Filament</th>
                            <th class="p-1">Voir page BDFI</th>
                            <th colspan=3 class="p-1">Chercher sur :</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($auteurs as $author)
                        <tr class="border border-blue-500">
                            <td class="py-1 px-2 border border-blue-500">{{ $author->name }} {{ $author->first_name }}</td>
                            <td class="py-1 px-2 border border-blue-500 text-red-500"><strong>{{ $author->birth_date }}</strong></td>
                            <td class="py-1 px-2 border border-blue-500">{{ $author->date_death }}</td>
                            <td class="py-1 px-2 border border-blue-500">
                                <x-admin.link-ext lien='/filament/authors/{{ $author->id }}'>Fiche {{ $author->id }}</x-admin.link-ext>
                            </td>
                            <td class="py-1 px-2 border border-blue-500">
                                <x-admin.link-ext lien='/auteurs/{{ $author->id }}'>{{ $author->first_name }} {{ $author->name }}</x-admin.link-ext>
                            </td>
                            <td class="py-1 px-2">
                                <x-admin.link-ext lien='http://catalogue.bnf.fr/resultats-auteur.do?nomAuteur={{ $author->name }}, {{ $author->first_name }}&filtre=1&pageRech=rau'>BNF</x-admin.link-ext>
                            </td>
                            <td class="py-1 px-2">
                                <x-admin.link-ext lien='http://www.isfdb.org/cgi-bin/se.cgi?arg={{ $author->first_name }} {{ $author->name }}&type=Name'>ISFDB</x-admin.link-ext>
                            </td>
                            <td class="py-1 px-2">
                                <x-admin.link-ext lien='https://www.google.fr/search?q={{ $author->first_name }} {{ $author->name }}'> Google </x-admin.link-ext>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="p-2 text-xl">Dates de décès potentiellement étranges...</div>

                <table class="border-collapse border border-blue-800 w-full">
                    <thead>
                        <tr class="bg-blue-50">
                            <th class="p-1">Nom</th>
                            <th class="p-1">Naissance</th>
                            <th class="p-1">Décès</th>
                            <th class="p-1">Fiche Filament</th>
                            <th class="p-1">Voir page BDFI</th>
                            <th colspan=3 class="p-1">Chercher sur :</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($auteurs2 as $author)
                        <tr class="border border-blue-500">
                            <td class="py-1 px-2 border border-blue-500">{{ $author->name }} {{ $author->first_name }}</td>
                            <td class="py-1 px-2 border border-blue-500">{{ $author->birth_date }}</td>
                            <td class="py-1 px-2 border border-blue-500 text-red-500"><strong>{{ $author->date_death }}</strong></td>
                            <td class="py-1 px-2 border border-blue-500">
                                <x-admin.link-ext lien='/filament/authors/{{ $author->id }}'>Fiche {{ $author->id }}</x-admin.link-ext>
                            </td>
                            <td class="py-1 px-2 border border-blue-500">
                                <x-admin.link-ext lien='/auteurs/{{ $author->id }}'>{{ $author->first_name }} {{ $author->name }}</x-admin.link-ext>
                            </td>
                            <td class="py-1 px-2">
                                <x-admin.link-ext lien='http://catalogue.bnf.fr/resultats-auteur.do?nomAuteur={{ $author->name }}, {{ $author->first_name }}&filtre=1&pageRech=rau'>BNF</x-admin.link-ext>
                            </td>
                            <td class="py-1 px-2">
                                <x-admin.link-ext lien='http://www.isfdb.org/cgi-bin/se.cgi?arg={{ $author->first_name }} {{ $author->name }}&type=Name'>ISFDB</x-admin.link-ext>
                            </td>
                            <td class="py-1 px-2">
                                <x-admin.link-ext lien='https://www.google.fr/search?q={{ $author->first_name }} {{ $author->name }}'> Google </x-admin.link-ext>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
