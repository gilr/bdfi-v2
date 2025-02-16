<x-app-layout>
    <!-- Page d'accueil administration (jetstream) -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="/admin">Administration BDFI</a> &rarr;
            <a href="/admin/outils">Outils & Rapports</a> &rarr;
            Manque récompenses prix
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class='bg-white overflow-hidden shadow-xl sm:rounded-lg p-4'>

                <div class="p-2 text-xl">Prix n'ayant pas de lauréat en {{ $date }} ou avant</div>

                <div class="border border-yellow-800 bg-yellow-100 mb-2 rounded-lg p-2">
                    <p>Attention, même si une seule catégorie est complétée pour les années manquantes, le prix complet va disparaître des rapports de prix manquants.</p>
                    <p>Complétez donc de préférence année par année, et toutes les catégories d'un prix pour une année donnée.</p>
                </div>

                <table class="table-auto border-collapse border border-blue-800 w-full">
                    <thead>
                        <tr class="bg-blue-50">
                            <th class="p-1">Award</th>
                            <th class="p-1">Dernier lauréat</th>
                            <th class="p-1">Liens sur catégories</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($results as $result)
                        <tr class="border border-blue-500">
                            <td class="py-1 px-2 border border-blue-500">{{ $result[0]->name }}</td>
                            <td class="py-1 px-2 border border-blue-500">{{ $result[1] }}</td>
                            <td>
                                @foreach ($result[2] as $categ)
                                    <x-admin.link-ext lien='/filament/award-categories/{{ $categ->id }}/edit?activeRelationManager=0'>{{ $categ->name }}<br /></x-admin.link-ext>
                                @endforeach
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
