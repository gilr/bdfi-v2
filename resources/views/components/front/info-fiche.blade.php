@auth
    @if (auth()->user()->hasGuestRole())
        <div class='text-sm p-2 my-2 rounded-sm lg:mx-40 self-center border text-blue-900 bg-sky-200 shadow-md shadow-sm shadow-blue-600'>
            Vous êtes authentifié (<b>{{ auth()->user()->name }}</b>) et vous êtes {{ auth()->user()->role->getLabel() }}
            BDFI (role <i>'{{ auth()->user()->role->value }}'</i>).
            <br />
            @if (auth()->user()->isGuest())
                Vous n'avez qu'un accès en lecture, pas de modifications possibles.
            @endif
            @if (auth()->user()->hasMemberRole())
                Vous pouvez modifier la <a class="text-red-700" href="/filament/{{ $filament }}/{{ $results->id }}" target="_blank">fiche {{ $area }} <span class="font-bold">{{ ($results->full_name ?: $results->name) }} </span></a> (s'ouvre dans un nouvel onglet).<br />
                <br />
                - Fiche racine créée le {{ $results->updated_at }} par {{ $results->creator->name }}
                <br />- Fiche racine mise à jour le {{ $results->updated_at }} par {{ $results->editor->name }}
                <br />
                @if (isset($results->nom_bdfi) && ($results->nom_bdfi <> ""))
                    <div class='text-xs'>
                        - Nom BDFI (temporaire) : <span class='font-semibold'>{{ $results->nom_bdfi }}</span>
                    </div>
                @endif
                @if (isset($results->sigle_bdfi) && ($results->sigle_bdfi <> ""))
                    <div class='text-xs'>
                        - Sigle BDFI (temporaire) : <span class='font-semibold'>{{ $results->sigle_bdfi }}</span>
                    </div>
                @endif
                @if (isset($results->quality))
                    <div class='text-xs'>
                        - Qualité fiche  : <span class='font-semibold'>{{ $results->quality->getLabel() }}</span>
                    </div>
                @endif

                @if (isset($results->private))
                    @if ($results->private)
                        <div class='text-xs'>- Infos internes/privées/de travail :</div>
                        <div class='text-xs my-2 p-1 border border-yellow-500 bg-yellow-50'>
                            {!! $results->private !!}
                        </div>
                    @else
                        <div class='text-xs'>- Pas d'infos internes/privées/de travail.</div>
                    @endif
                @endif
            @endif
        </div>
    @endif
@endauth