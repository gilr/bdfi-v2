<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Mancoliste') }}
    </h2>
</x-slot>

<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
@php
$user = Auth::user();
$user->loadMissing([
    'collections_en_cours.publisher', 'collections_en_cours.publications',
    'collections_quasi_ok.publisher', 'collections_quasi_ok.publications',
]);
// Inclure les ouvrages possédés mais abîmés ? (bascule en haut de page, paramètre ?abimes=1)
$withDamaged = request()->boolean('abimes');
// Collections recherchées ayant au moins un ouvrage manquant (les complètes ne sont pas affichées)
$mancoCollections = $user->collections_en_cours->concat($user->collections_quasi_ok)
    ->map(fn ($collection) => ['collection' => $collection, 'missing' => $user->missingInCollection($collection, $withDamaged)])
    ->filter(fn ($item) => $item['missing']->isNotEmpty());
@endphp

    <div class="border-b-2 border-blue-400">
        <a class="font-semibold bg-blue-200 border px-2 py-1 m-2 border-blue-600 hover:bg-purple-100 hover:text-purple-800 hover:border-blue-800 rounded-t shadow-lg" href="/user/bibliotheque">En bref</a>
        <a class="font-semibold bg-blue-200 border px-2 py-1 m-2 border-blue-600 hover:bg-purple-100 hover:text-purple-800 hover:border-blue-800 rounded-t shadow-lg" href="/user/gestion-biblio">Gestion</a>
        <a class="font-semibold bg-blue-200 border px-2 py-1 m-2 border-blue-600 hover:bg-purple-100 hover:text-purple-800 hover:border-blue-800 rounded-t shadow-lg" href="/user/affiche-collection">Listing</a>
        <span class="font-semibold bg-gray-100 border px-2 py-1 m-2 border-gray-600 rounded-t shadow-lg">Mancoliste</span>
    </div>

    @if (count($user->collections) == 0)
        @include('user._biblioempty', ['emptyMessage' => "Vous ne suivez aucune collection pour l'instant."])
    @elseif ((count($user->collections_en_cours) + count($user->collections_quasi_ok)) == 0)
        @include('user._biblioempty', ['emptyMessage' => "Vos collections suivies sont en pause, complètes ou cachées : il n'y a pas d'ouvrage manquant à afficher."])
    @else
    {{-- Bascule : chaque changement recharge la page --}}
    <div class="pt-3">
        <label class="inline-flex items-center gap-2" style="cursor: pointer;">
            <input type="checkbox" class="rounded border-blue-600 text-blue-600" @checked($withDamaged)
                onchange="window.location.href = '/user/mancoliste' + (this.checked ? '?abimes=1' : '')">
            Inclure les ouvrages possédés mais abîmés <x-front.display-icon-pub-damaged />
            @if ($withDamaged)
                <span class="text-sm text-gray-600">(affichés <span class="italic" style="background-color: #e5e7eb; -webkit-print-color-adjust: exact; print-color-adjust: exact;">en italique sur fond gris</span>)</span>
            @endif
        </label>
    </div>
    @if ($mancoCollections->isEmpty())
        @include('user._biblioempty', ['emptyMessage' => $withDamaged
            ? "Il ne vous manque aucun ouvrage, et aucun n'est abîmé, dans vos collections en cours."
            : "Il ne vous manque aucun ouvrage dans vos collections en cours."])
    @else
    <div>
        <div class='py-2'>
            Ne sont affichés évidemment que les ouvrages manquants des listes de collections suivies et recherchées (non complètes, non en pause, non cachées) :
        </div>
        <div class=' columns-1 lg:columns-2 xl:columns-3 text-base gap-2 self-center sm:w-full'>
            @foreach ($mancoCollections as $item)
                @php
                    $collection = $item['collection'];
                    $missing = $item['missing'];
                @endphp
                <div class='border border-blue-600 mb-1 break-inside-avoid text-center'>
                    {{-- Titre sur fond bleuté (conservé à l'impression) --}}
                    <div class="font-semibold py-0.5 border-b border-blue-600" style="background-color: #dbeafe; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                        <x-front.display-icon-pub-missing />
                        <a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/collections/{{ $collection->slug }}'>{{ $collection->name }}</a> - {{ $collection->publisher->name }}
                    </div>
                    @include('user._mancobloc')
                </div>
            @endforeach
        </div>
    </div>
    @endif
    @endif

            </div>
        </div>
    </div>
</div>

</x-app-layout>
