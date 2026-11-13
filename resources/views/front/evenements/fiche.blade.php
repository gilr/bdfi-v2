<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
    <div class='text-base mt-2 font-semibold bg-yellow-50'>
         {{ $results->name }}
    </div>

    <div class='text-base'>
        Type : <span class='font-semibold'>{{ $results->type->getLabel() }}</span>
    </div>

    @if ($results->periodicity)
        <div class='text-base'>
            Périodicité : <span class='font-semibold'>{{ $results->periodicity->getLabel() }}</span>
        </div>
    @endif

    @if ($results->year_start || $results->year_end)
        <div class='text-base'>
            @if ($results->year_start)
                Depuis <span class='font-semibold'>{{ $results->year_start }}</span>
            @endif
            @if ($results->year_end)
                {{ $results->year_start ? 'jusqu\'en' : 'Jusqu\'en' }} <span class='font-semibold'>{{ $results->year_end }}</span>
            @else
                (toujours actif)
            @endif
        </div>
    @endif

    @if ($results->place)
        <div class='text-base'>
            Lieu{{ $results->occurrences->count() > 1 ? ' habituel' : '' }} : <span class='font-semibold'>{{ $results->place }}</span>
        </div>
    @endif

    @if ($results->url)
        <div class='text-base'>
            URL : <a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href="{{ $results->url }}">{{ $results->url }}</a>
        </div>
    @endif

    <div class='text-base'>
        Scope : {!! ($results->is_full_scope ? "Imaginaire <b>et</b> littérature" : "Imaginaire <b>ou</b> littérature") !!}
    </div>

    @if ($results->information)
        <div class='text-base my-4 p-2 border border-yellow-500 bg-yellow-50'>
            {!! $results->information !!}
        </div>
    @endif

    @if ($results->occurrences->count() > 0)
        <div class='text-base mt-4 font-semibold'>
            {{ $results->occurrences->count() > 1 ? 'Éditions' : 'Édition' }} :
        </div>

        {{-- Les plus récentes d'abord --}}
        @foreach ($results->occurrences->sortByDesc('start_date') as $occurrence)
            <div class='text-base mt-2 ml-2 p-2 border-l-4 {{ $occurrence->end_date >= today() ? 'border-green-600' : 'border-zinc-300' }}'>
                <span class='font-semibold'>{{ $occurrence->full_name }}</span>
                : du {{ $occurrence->start_date->format("j/m/Y") }} au {{ $occurrence->end_date->format("j/m/Y") }}
                {{-- Lieu affiché seulement s'il précise ou remplace le lieu habituel --}}
                @if ($occurrence->place && ($occurrence->place != $results->place))
                    - {{ $occurrence->place }}
                @endif
                @if ($occurrence->end_date >= today())
                    - {{ ($occurrence->is_confirmed ? "confirmé" : "non encore confirmé") }}
                @endif
                {{-- Information affichée seulement si elle diffère de celle de l'évènement (reprise des données de la phase 1) --}}
                @if ($occurrence->information && ($occurrence->information != $results->information))
                    <div class='mt-1'>{!! $occurrence->information !!}</div>
                @endif
            </div>
        @endforeach
    @endif
</div>
