<x-app-layout>
    <!-- Page d'accueil administration (jetstream) -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="/admin">Administration BDFI</a> &rarr;
            <a href="/admin/formulaires">Formulaires</a> &rarr;
            Liste des programmations futures
        </h2>
    </x-slot>

    <div class="pb-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4">

                <div class="border border-yellow-800 bg-yellow-100 rounded-lg p-2">
                    <p>Cet écran permet de lister les publications annoncées, et après avoir suivi le lien fiche, d'en compléter les données (collection, auteurs, sommaire, autres infos...), et éventuellement en profiter pour réviser si besoin des dates programmées.</p>
                    <p>Dans un premier, temps, ces actions se font via la fiche table. Suivre le lien <span class="font-semibold">qui s'ouvre dans un autre onglet</span> pour :</p>
                    <ul class="list-disc pl-6">
                        <li>Si besoin, passer en modification (bouton "Modifier") puis changer l'état de publication ("A paraître" -> "Paru") ou ajuster la date de publication</li>
                        <li>Compléter les informations de la fiche</li>
                        <li>Attacher à une collection existante, ou la créer</li>
                        <li>Si pas encore fait, attacher le ou les auteurs - Si inexistant, il faudra le créer au préalable</li>
                        <li>Si pas encore fait, attacher ou créer le contenu - Si le ou les titres ne sont jamais parus, il faudra le/les créer au préalable</li>
                    </ul>
                    <p>Plus tard, certaines actions pourront directement être réalisées depuis cet écran (ajuster ou repousser la date de publication).</p>
                </div>

                <div class="p-2 text-xl">Dates de naissance potentiellement étranges...</div>

                <table class="table-auto border-collapse border border-blue-800 w-full">
                    <thead>
                        <tr class="bg-blue-50">
                            <th class="p-1">Titre ouvrage proposé et accès fiche</th>
                            <th class="p-1">Date annoncée</th>
                            <th class="p-1">Type</th>
                            <th class="p-1">Support</th>
                            <th class="p-1">Création fiche</th>
                            <th class="p-1">Modification fiche</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($results as $publication)
                        <tr class="border border-blue-500">
                            <td class="py-1 px-2 border border-blue-500"><x-admin.link-ext lien='/filament/publications/{{ $publication->id }}'>{{ $publication->name }}</x-admin.link-ext></td>
                            <td class="py-1 px-2 border border-blue-500">{{ $publication->approximate_parution }}</td>
                            <td class="py-1 px-2 border border-blue-500">{{ $publication->type }}</td>
                            <td class="py-1 px-2 border border-blue-500">{{ $publication->support }}</td>
                            <td class="py-1 px-2 border border-blue-500">{{ $publication->created_at }} </td>
                            <td class="py-1 px-2 border border-blue-500">{{ $publication->updated_at }} </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</x-app-layout>
