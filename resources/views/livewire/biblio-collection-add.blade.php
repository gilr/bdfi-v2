<div>
    <label for="biblio-collection-search" class="font-semibold">Ajouter le suivi d'une collection :</label>
    <input id="biblio-collection-search" type="text" wire:model.live.debounce.400ms="search" autocomplete="off"
        class="p-1 m-1 w-full sm:w-96 bg-blue-50 border border-blue-400 rounded focus:border-blue-700 focus:ring-blue-300"
        placeholder="Extrait du nom de la collection (2 caractères minimum)...">
    <span wire:loading class="text-sm text-gray-500">Recherche...</span>

    @if (mb_strlen(trim($search)) >= 2)
        <div class="mt-1 ml-1 sm:ml-4 border border-blue-300 rounded bg-blue-50 p-2">
            @if ($collections->isEmpty())
                Aucune collection trouvée pour "{{ trim($search) }}".
            @else
                <ul>
                @foreach ($collections as $collection)
                    <li class="py-0.5 border-b border-blue-100 last:border-b-0" wire:key="biblio-add-{{ $collection->id }}">
                        @if (in_array($collection->id, $followed))
                            <span class="inline-block w-20 text-center text-sm text-gray-500 italic">déjà suivie</span>
                        @else
                            <button type="button" wire:click="add({{ $collection->id }})" wire:loading.attr="disabled"
                                class="w-20 bg-blue-400 font-semibold border border-blue-800 rounded px-2 hover:bg-blue-500 shadow">
                                Suivre
                            </button>
                        @endif
                        {{ $collection->name }}
                        @if ($collection->publisher)
                            <span class="text-gray-600">- {{ $collection->publisher->name }}</span>
                        @endif
                        <a class="text-sm text-purple-700 hover:underline" href="/collections/{{ $collection->slug }}" target="_blank" title="Ouvrir la fiche de la collection dans un nouvel onglet">(fiche)</a>
                    </li>
                @endforeach
                </ul>
                @if ($more)
                    <div class="mt-1 text-sm text-gray-600">
                        Seuls les {{ $collections->count() }} premiers résultats sont affichés : précisez la recherche.
                    </div>
                @endif
            @endif
        </div>
    @endif
</div>
