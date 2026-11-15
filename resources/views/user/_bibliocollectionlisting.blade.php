{{-- Bloc d'une collection dans l'onglet Listing (décompte mis à jour sans rechargement, évènement biblio-count). Marge basse seulement (mb-1) : en colonnes CSS, une marge haute ne s'applique qu'au bloc en tête de la 1re colonne, ce qui le décalait vers le bas --}}
<div class='border border-blue-600 mb-1 break-inside-avoid p-1' x-data="{ ownedCount: {{ $user->countOwnedInCollection($collection) }} }" x-on:biblio-count="ownedCount += $event.detail.delta">
    <a class='border-b border-dotted border-purple-700 font-semibold hover:text-purple-700 focus:text-purple-900' href='/collections/{{ $collection->slug }}'>{{ $collection->name }}</a> - {{ $collection->publisher->name }}
    <span class="text-sm text-gray-600 whitespace-nowrap">(<span x-text="ownedCount">{{ $user->countOwnedInCollection($collection) }}</span> / {{ count($collection->publications) }})</span>
    @include('user._bibliocollectionbloc')
</div>
