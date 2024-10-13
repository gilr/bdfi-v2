    <div class='text-base my-4 bold self-center'>
        En bref, les prochains évènements confirmés, et ciblés littérature et imaginaire :
    </div>

    <div class='text-base mx-2 my-1 md:my-2'>
        <ul class="list-disc pl-4 ml-4">
        @foreach($results as $result)
            @if (($result->is_confirmed == true) &&
                 ($result->is_full_scope == true) &&
                 ($result->end_date >= date("Y-m-d")))
                 <li>
                    <x-front.lien-event link='/evenements/{{ $result->slug }}'>{{ $result->name }}</x-front.lien-event>
                    : {{ $result->type->getLabel() }}, du {{ StrDateformatClair($result->start_date, 0) }} au {{ StrDateformatClair($result->end_date, 0) }}
                 </li>
            @endif
        @endforeach
        </ul>
    </div>

    <div class='text-base my-4 bold self-center'>
        En détail, et élargi aux évènements plus généralistes ou non encore confirmés :
    </div>

    <div class='text-base mx-2 my-1 md:my-2'>
        @foreach($results as $result)
            @if ($result->end_date >= date("Y-m-d"))
                <div class='text-base mt-1 mr-6 mb-2 ml-6 p-3 rounded-t-lg border border-green-600 bg-green-100'>
                    <x-front.lien-event link='/evenements/{{ $result->slug }}'>{{ $result->name }}</x-front.lien-event>
                    ({{ $result->type->getLabel() }}) du {{ StrDateformatClair($result->start_date, 0) }} au {{ StrDateformatClair($result->end_date, 0) }}
                    @if ($result->place !== '')
                        {{ $result->place }}
                    @endif
                 </div>
                 <div class='text-base -mt-2.5 mr-6 mb-2 ml-6 p-3 rounded-b-lg border border-green-600 shadow-md shadow-zinc-800/80'>
                    @if ($result->is_full_scope == false)
                        <b>En marge ou plus généraliste - </b>
                    @endif

                    @if ($result->is_confirmed == false) {
                        <b>A confirmer - </b>
                    @endif

                    {{ $result->information }}
                    @if ($result->is_confirmed == false)
                       <br /><br /><b>Note :</b> Cette information est à vérifier; nous ne savons pas si l'évènement est maintenu et si les dates sont confirmées.
                    @endif
                 </div>
            @endif
        @endforeach
    </div>

