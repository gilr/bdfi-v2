<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Bibliothèque') }}
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
        <span class="font-semibold bg-gray-200 border px-2 py-1 m-2 border-gray-600 rounded-t shadow-lg">Gestion</span>
        <a class="font-semibold bg-blue-200 border px-2 py-1 m-2 border-blue-600 hover:bg-purple-100 hover:text-purple-800 hover:border-blue-800 rounded-t shadow-lg" href="/user/affiche-collection">Listing</a>
        <a class="font-semibold bg-blue-200 border px-2 py-1 m-2 border-blue-600 hover:bg-purple-100 hover:text-purple-800 hover:border-blue-800 rounded-t shadow-lg" href="/user/mancoliste">Mancoliste</a>
    </div>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 pt-2">
        <livewire:collection-search intro="" label="Trouver numéro de collection" withId="YES" />
    </div>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 pt-2">
        Attention, l'ajout du suivi d'une collection se fait pour l'instant via son numéro de fiche (à récupérer ci-dessus). Dans la prochaine version, l'ajout se fera directement, plus facilement.<br />
        {{ html()->form('POST', '/user/ajouter-collection')->open() }}
            ID (numéro fiche) de collection :
            {{ html()->text($name = "col")->size(5)->class("p-1 m-1 bg-blue-200") }}
            {{ html()->submit($text = "Ajouter collection")->class("bg-blue-400 font-semibold border border-blue-800 rounded p-1 m-1 shadow-lg") }}
            {{ html()->form()->close() }}
    </div>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 pt-2 border-b border-gray-400 pb-2">
        L'ajout (ou le retrait) des ouvrages possédés se fait dans l'onglet "Listing".
    </div>

    @if (count($user->collections) == 0)
        Aucune collection suivie pour l'instant.
    @else

        @if (count($user->collections_en_cours) != 0)
            <div class='pt-2'>
                Collections suivies et en cours :
            </div>
            @foreach ($user->collections_en_cours as $collection)
                @include('user._bibliocollectionline')
            @endforeach
        @endif

        @if (count($user->collections_quasi_ok) != 0)
            <div class='pt-2'>
                Collections suivies et presque OK :
            </div>
            @foreach ($user->collections_quasi_ok as $collection)
                @include('user._bibliocollectionline')
            @endforeach
        @endif

        @if (count($user->collections_terminees) != 0)
            <div class='pt-2'>
                Collections suivies et terminées :
            </div>
            @foreach ($user->collections_terminees as $collection)
                @include('user._bibliocollectionline')
            @endforeach
        @endif

        @if (count($user->collections_en_pause) != 0)
            <div class='pt-2'>
                Collections en pause (plus suivies pour l'instant) :
            </div>
            @foreach ($user->collections_en_pause as $collection)
                @include('user._bibliocollectionline')
            @endforeach
        @endif

        @if (count($user->collections_cachees) != 0)
            <div class='pt-2'>
                Collections cachées :
            </div>
            @foreach ($user->collections_cachees as $collection)
                @include('user._bibliocollectionline')
            @endforeach
        @endif
    @endif

            </div>
        </div>
    </div>
</div>

</x-app-layout>