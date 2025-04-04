<x-app-layout>
    <!-- Page d'accueil administration (jetstream) -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="/admin">Administration BDFI</a> &rarr;
            <a href="/admin/outils">Outils & Rapports</a> &rarr;
            Fiches d\'avancement à l'état {{ $level }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4">

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
                            <td class="py-1 px-2 border border-blue-500"><strong>{{ $author->birth_date }}</strong></td>
                            <td class="py-1 px-2 border border-blue-500">{{ $author->date_death }}</td>
                            <td class="py-1 px-2 border border-blue-500">
                                <x-admin.link-ext lien='/filament/authors/{{ $author->id }}'>Fiche Filament  {{ $author->id }}</x-admin.link-ext>
                            </td>
                            <td class="py-1 px-2 border border-blue-500">
                                <x-admin.link-ext lien='/auteurs/{{ $author->slug }}'>{{ $author->first_name }} {{ $author->name }}</x-admin.link-ext>
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
                <div class="text-center">
                    {!! str_replace('/?', '?', $auteurs->render()) !!}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
