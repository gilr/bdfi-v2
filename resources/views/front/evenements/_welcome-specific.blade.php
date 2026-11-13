    {{-- $occurrences : occurrences en cours ou à venir, avec leur évènement (cf. EventController::welcome) --}}
    <div class='text-base my-4 bold self-center'>
        En bref, les prochains évènements confirmés, et ciblés littérature et imaginaire :
    </div>

    <div class='text-base mx-2 my-1 md:my-2'>
        <ul class="list-disc pl-4 ml-4">
        @foreach($occurrences as $occurrence)
            @if (($occurrence->is_confirmed == true) && ($occurrence->event->is_full_scope == true))
                 <li>
                    <x-front.lien-event link='/evenements/{{ $occurrence->event->slug }}'>{{ $occurrence->full_name }}</x-front.lien-event>
                    : {{ $occurrence->event->type->getLabel() }}, du {{ StrDateformatClair($occurrence->start_date, 0) }} au {{ StrDateformatClair($occurrence->end_date, 0) }}
                 </li>
            @endif
        @endforeach
        </ul>
    </div>

    <div class='text-base my-4 bold self-center'>
        En détail, et élargi aux évènements plus généralistes ou non encore confirmés :
    </div>

    <div class='text-base mx-2 my-1 md:my-2'>
        @foreach($occurrences as $occurrence)
            <div class='text-base mt-1 mr-6 mb-2 ml-6 p-3 rounded-t-lg border border-green-600 bg-green-100'>
                <x-front.lien-event link='/evenements/{{ $occurrence->event->slug }}'>{{ $occurrence->full_name }}</x-front.lien-event>
                ({{ $occurrence->event->type->getLabel() }}) du {{ StrDateformatClair($occurrence->start_date, 0) }} au {{ StrDateformatClair($occurrence->end_date, 0) }}
                @if ($occurrence->effective_place)
                    {{ $occurrence->effective_place }}
                @endif
             </div>
             <div class='text-base -mt-2.5 mr-6 mb-2 ml-6 p-3 rounded-b-lg border border-green-600 shadow-md shadow-zinc-800/80'>
                @if ($occurrence->event->is_full_scope == false)
                    <b>En marge ou plus généraliste - </b>
                @endif

                @if ($occurrence->is_confirmed == false)
                    <b>A confirmer - </b>
                @endif

                {{ $occurrence->information ?: $occurrence->event->information }}
                @if ($occurrence->is_confirmed == false)
                   <br /><br /><b>Note :</b> Cette information est à vérifier; nous ne savons pas si l'évènement est maintenu et si les dates sont confirmées.
                @endif
             </div>
        @endforeach
    </div>

