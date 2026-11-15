<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="/admin">Administration BDFI</a> &rarr;
            <a href="/admin/rapports">Rapports</a> &rarr;
            Erreurs sur les variantes de textes
        </h2>
    </x-slot>

    <div class="pb-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4">

                <div class="border border-yellow-800 bg-yellow-100 rounded-lg p-2">
                    Textes dont le rattachement parent/variantes est incohérent : {{ $titles->count() }} texte(s).<br />
                   « Analyser » ouvre le formulaire de gestion des variantes sur le texte concerné.
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4">
                @if ($titles->isEmpty())
                    <div class="p-2 bg-green-100 border border-green-800 rounded">Aucune erreur détectée.</div>
                @else
                    <table class="table-auto border-collapse border border-blue-800 w-full text-sm">
                        <thead>
                            <tr class="bg-blue-50">
                                <th class="p-1">Texte</th>
                                <th class="p-1">Erreur</th>
                                <th class="p-1">Analyse</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($titles as $title)
                                <tr class="border border-blue-500">
                                    <td class="py-1 px-2 border border-blue-500">
                                        <x-admin.title-summary :title="$title" />
                                    </td>
                                    <td class="py-1 px-2 border border-blue-500">
                                        @foreach ($errors[$title->id] as $label)
                                            <div>{{ $label }}@if ($label === 'Type de variante inconnu') (« {{ $title->variant_type }} »)@endif</div>
                                        @endforeach
                                    </td>
                                    <td class="py-1 px-2 border border-blue-500" style="white-space: nowrap">
                                        <x-admin.link lien='/admin/formulaires/gerer-variantes?titre={{ $title->id }}'>analyser</x-admin.link>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
