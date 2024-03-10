<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Listing collection') }}
    </h2>
</x-slot>

<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
@php
$user = Auth::user()
@endphp

    <div class="border-b-2 border-blue-400">
        <a class="font-semibold bg-blue-200 border px-2 py-1 m-2 border-blue-600 hover:bg-purple-100 hover:text-purple-800 hover:border-blue-800 rounded-t shadow-lg" href="/user/bibliotheque">En bref</a>
        <a class="font-semibold bg-blue-200 border px-2 py-1 m-2 border-blue-600 hover:bg-purple-100 hover:text-purple-800 hover:border-blue-800 rounded-t shadow-lg" href="/user/gestion-biblio">Gestion</a>
        <a class="font-semibold bg-blue-200 border px-2 py-1 m-2 border-blue-600 hover:bg-purple-100 hover:text-purple-800 hover:border-blue-800 rounded-t shadow-lg" href="/user/affiche-collection">Listing</a>
        <span class="font-semibold bg-gray-100 border px-2 py-1 m-2 border-gray-600 rounded-t shadow-lg">Mancoliste</span>
    </div>

    @if (count($user->collections) == 0)
        Aucune collection suivie pour l'instant.
    @else
    <div>
        @if ((count($user->collections_en_cours) + count($user->collections_quasi_ok)) != 0)
            <div class='py-2'>
                Ne sont affichées évidemment que les ouvrages manquants des listes de collections suivies et recherchées (non complètes, non en pause, non cachées) :
            </div>
            <div class=' columns-1 lg:columns-2 xl:columns-3 text-base gap-2 self-center sm:w-full'>
                @foreach ($user->collections_en_cours as $collection)
                    <div class='border border-blue-600 my-1 break-inside-avoid text-center'>
                        {{ $collection->name }} - {{ $collection->publisher->name }}
                        @include('user._mancobloc')
                    </div>
                @endforeach
                @foreach ($user->collections_quasi_ok as $collection)
                    <div class='border border-blue-600 my-1 break-inside-avoid text-center'>
                        {{ $collection->name }} - {{ $collection->publisher->name }}
                        @include('user._mancobloc')
                    </div>
                @endforeach
            </div>
        @endif

    </div>
    @endif

            </div>
        </div>
    </div>
</div>

</x-app-layout>