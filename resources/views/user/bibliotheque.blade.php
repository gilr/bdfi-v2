<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Bibliothèque') }}
    </h2>
</x-slot>

<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

@php
$user = Auth::user()
@endphp

@if (count($user->collections) == 0)
    Aucune collection suivie pour l'instant.
@else
    <div>
        Collections suivies et en cours :
    </div>
    @forelse ($user->collections_en_cours as $collection)
        <div class='ml-2 md:ml-8'>
            <a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/collections/{{ $collection->id }}'>{{ $collection->name }} </a> - {{ $collection->publisher->name }}
        </div>
    @empty
        néant
    @endforelse
    <div>
        Collections presque OK :
    </div>
    @forelse ($user->collections_quasi_ok as $collection)
        <div class='ml-2 md:ml-8'>
            {{ $collection->pivot->status }} : <a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/collections/{{ $collection->id }}'>{{ $collection->name }} </a> - {{ $collection->publisher->name }}
        </div>
    @empty
        néant
    @endforelse
    <div>
        Collections terminées :
    </div>
    @forelse ($user->collections_terminees as $collection)
        <div class='ml-2 md:ml-8'>
            {{ $collection->pivot->status }} : <a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/collections/{{ $collection->id }}'>{{ $collection->name }} </a> - {{ $collection->publisher->name }}
        </div>
    @empty
        néant
    @endforelse
    <div>
        Collections en pause :
    </div>
    @forelse ($user->collections_en_pause as $collection)
        <div class='ml-2 md:ml-8'>
            {{ $collection->pivot->status }} : <a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/collections/{{ $collection->id }}'>{{ $collection->name }} </a> - {{ $collection->publisher->name }}
        </div>
    @empty
        néant
    @endforelse
    <div>
        Collections cachées :
    </div>
    @forelse ($user->collections_cachees as $collection)
        <div class='ml-2 md:ml-8'>
            {{ $collection->pivot->status }} : <a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/collections/{{ $collection->id }}'>{{ $collection->name }} </a> - {{ $collection->publisher->name }}
        </div>
    @empty
        néant
    @endforelse
@endif

        </div>
    </div>
</div>

</x-app-layout>