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
        <span class="font-semibold bg-gray-100 border px-2 py-1 m-2 border-gray-600 rounded-t shadow-lg">En bref</span>
        <a class="font-semibold bg-blue-200 border px-2 py-1 m-2 border-blue-600 hover:bg-purple-100 hover:text-purple-800 hover:border-blue-800 rounded-t shadow-lg" href="/user/gestion-biblio">Gestion</a>
        <a class="font-semibold bg-blue-200 border px-2 py-1 m-2 border-blue-600 hover:bg-purple-100 hover:text-purple-800 hover:border-blue-800 rounded-t shadow-lg" href="/user/affiche-collection">Listing</a>
        <a class="font-semibold bg-blue-200 border px-2 py-1 m-2 border-blue-600 hover:bg-purple-100 hover:text-purple-800 hover:border-blue-800 rounded-t shadow-lg" href="/user/mancoliste">Mancoliste</a>
    </div>

    @if (count($user->collections) == 0)
        Aucune collection suivie pour l'instant.
    @else
        <div class='pt-2'>
            Collections suivies et en cours :
        </div>
        @forelse ($user->collections_en_cours as $collection)
            <div class='ml-2 md:ml-8 py-0.5 my-0.5'>
                <a class='border-b border-dotted border-purple-700 font-semibold hover:text-purple-700 focus:text-purple-900' href='/collections/{{ $collection->id }}'>{{ $collection->name }} </a> - {{ $collection->publisher->name }} - xxx / {{ count($collection->publications) }}
            </div>
        @empty
            <span class='italic ml-2 md:ml-8'>aucune</span>
        @endforelse

        <div class='pt-2'>
            Collections suivies et presque OK :
        </div>
        @forelse ($user->collections_quasi_ok as $collection)
            <div class='ml-2 md:ml-8 py-0.5 my-0.5'>
                <a class='border-b border-dotted border-purple-700 font-semibold hover:text-purple-700 focus:text-purple-900' href='/collections/{{ $collection->id }}'>{{ $collection->name }} </a> - {{ $collection->publisher->name }} - xxx / {{ count($collection->publications) }}
            </div>
        @empty
            <span class='italic ml-2 md:ml-8'>aucune</span>
        @endforelse

        <div class='pt-2'>
            Collections suivies et terminées :
        </div>
        @forelse ($user->collections_terminees as $collection)
            <div class='ml-2 md:ml-8 py-0.5 my-0.5'>
                <a class='border-b border-dotted border-purple-700 font-semibold hover:text-purple-700 focus:text-purple-900' href='/collections/{{ $collection->id }}'>{{ $collection->name }} </a> - {{ $collection->publisher->name }} - xxx / {{ count($collection->publications) }}
            </div>
        @empty
            <span class='italic ml-2 md:ml-8'>aucune</span>
        @endforelse

        <div class='pt-2'>
            Collections en pause (plus suivies pour l'instant) :
        </div>
        @forelse ($user->collections_en_pause as $collection)
            <div class='ml-2 md:ml-8 py-0.5 my-0.5'>
                <a class='border-b border-dotted border-purple-700 font-semibold hover:text-purple-700 focus:text-purple-900' href='/collections/{{ $collection->id }}'>{{ $collection->name }} </a> - {{ $collection->publisher->name }} - xxx / {{ count($collection->publications) }}
            </div>
        @empty
            <span class='italic ml-2 md:ml-8'>aucune</span>
        @endforelse

        <div class='pt-2'>
            Collections cachées :
        </div>
        @forelse ($user->collections_cachees as $collection)
            <div class='ml-2 md:ml-8 py-0.5 my-0.5'>
                <a class='border-b border-dotted border-purple-700 font-semibold hover:text-purple-700 focus:text-purple-900' href='/collections/{{ $collection->id }}'>{{ $collection->name }} </a> - {{ $collection->publisher->name }} - xxx / {{ count($collection->publications) }}
            </div>
        @empty
            <span class='italic ml-2 md:ml-8'>aucune</span>
        @endforelse
    @endif

            </div>
        </div>
    </div>
</div>

</x-app-layout>