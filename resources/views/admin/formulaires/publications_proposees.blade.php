<x-app-layout>
    <!-- Page d'accueil administration (jetstream) -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="/admin">Administration BDFI</a> &rarr;
            <a href="/admin/formulaires">Formulaires</a> &rarr;
            Liste des propositions à valider (ou pas)
        </h2>
    </x-slot>

    <div class="pb-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4">

                <div class="border border-yellow-800 bg-yellow-100 rounded-lg p-2">
                    <p>La meilleure solution actuellement est de suivre le lien vers la fiche <span class="font-semibold">(qui s'ouvre dans un autre onglet)</span> pour :</p>
                    <ul class="list-disc pl-6">
                        <li>Lire et vérifier les infos apportées (liens, sources, etc) dans l'onglet "BDFI Infos" (en bas, informations de travail ou privées). En cas de doute, en discuter sur le forum.</li>
                        <li>Si OK, Passer en modification (bouton "Modifier") puis changer l'état de publication ("Proposition membre" &rarr; "Paru")</li>
                        <li>Si besoin, apporter les informations complémentaires fournies dans la zone texte pour :</li>
                    <ul class="list-decimal pl-6">
                        <li>Attacher à une collection existante, ou la créer</li>
                        <li>Attacher le ou les auteurs à la publi - Si inexistant, il faudra le/les créer au préalable</li>
                        <li>Attacher ou créer le contenu - Si le ou les titres ne sont jamais parus, il faudra le/les créer au préalable</li>
                    </ul>
                    </ul>
                </div>

                <div class="p-2 text-xl">Liste des propositions :</div>

                <table class="table-auto border-collapse border border-blue-800 w-full">
                    <thead>
                        <tr class="bg-blue-50">
                            <th class="p-1">Date proposition</th>
                            <th class="p-1">Titre ouvrage proposé et accès fiche</th>
                            <th class="p-1">Type</th>
                            <th class="p-1">Support</th>
                            <th class="p-1">Date parution</th>
                            <th class="p-1">Infos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($results as $publication)
                        <tr class="border border-blue-500">
                            <td class="py-1 px-2 border border-blue-500">{{ $publication->created_at }} </td>
                            <td class="py-1 px-2 border border-blue-500"><x-admin.link-ext lien='/filament/publications/{{ $publication->id }}'>{{ $publication->name }}</x-admin.link-ext></td>
                            <td class="py-1 px-2 border border-blue-500">{{ $publication->type }}</td>
                            <td class="py-1 px-2 border border-blue-500">{{ $publication->support }}</td>
                            <td class="py-1 px-2 border border-blue-500">{{ $publication->approximate_parution }}</td>
                            <td class="py-1 px-2 border border-blue-500">
                                {{ $publication->private }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</x-app-layout>
