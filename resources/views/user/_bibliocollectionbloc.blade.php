{{-- Ouvrages d'une collection (Listing) : ajout, retrait et état abîmé sans rechargement (biblioRow, voir affichecollection) --}}
@foreach ($collection->publications as $publication)
    @php
        $owned = $user->ownsPublication($publication->id);
        $damaged = $user->ownsDamagedPublication($publication->id);
    @endphp
    <div class='m-1 text-sm' x-data="biblioRow({{ $publication->id }}, {{ $owned ? 'true' : 'false' }}, {{ $damaged ? 'true' : 'false' }})"
        x-on:biblio-pub.window="sync($event.detail)">
        {{-- Possédé : livre (clic = bascule possédé <-> abîmé) et bouton de retrait --}}
        <span x-show="owned" @if(!$owned) x-cloak @endif>
            <button type="button" class="p-0 m-0 align-middle" style="cursor: pointer;" x-on:click="toggleDamaged()"
                x-bind:title="damaged ? 'Possédé mais abîmé : cliquer pour le marquer en bon état' : 'Possédé : cliquer pour le marquer abîmé'">
                <span x-show="!damaged" @if($damaged) x-cloak @endif><x-front.display-icon-pub-owned /></span>
                <span x-show="damaged" @if(!$damaged) x-cloak @endif><x-front.display-icon-pub-damaged /></span>
            </button>
            <button type="button" x-on:click="remove()" title="Retirer de ma bibliothèque"
                class="font-mono text-xs bg-red-200 font-semibold border border-red-800 rounded px-1 shadow-md m-0 shadow-red-800/40">-</button>
        </span>
        {{-- Non possédé : bouton d'ajout --}}
        <span x-show="!owned" @if($owned) x-cloak @endif>
            <span class="inline-block w-6"></span>
            <button type="button" x-on:click="add()" title="Ajouter à ma bibliothèque"
                class="font-mono text-xs bg-green-200 font-semibold border border-green-800 rounded px-1 shadow-md m-0 shadow-green-800/40">+</button>
        </span>

        @if($publication->pivot->number)
            {{ $publication->pivot->number }}.
        @endif
        <x-front.lien-ouvrage link='/ouvrages/{{ $publication->slug }}'>{{ $publication->name }}</x-front.lien-ouvrage>,

        @if(count($publication->authors) > 0)
            @foreach($publication->authors as $author)
                @if (!$loop->first)
                    ,
                @endif
                <x-front.lien-auteur link='/auteurs/{{ $author->slug }}'>{{ $author->name }}</x-front.lien-auteur>
            @endforeach
        @endif
    </div>
@endforeach
