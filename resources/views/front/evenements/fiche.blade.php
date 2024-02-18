<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
    <div class='text-base mt-2 font-semibold bg-yellow-50'>
         {{ $results->name }}
    </div>

    <div class='text-base'>
        Type : <span class='font-semibold'>{{ $results->type->getLabel() }}</span>
    </div>

    <div class='text-base'>
        Lieu : <span class='font-semibold'>{{ $results->place }}</span>
    </div>

    @if ($results->start_date)
        <div class='text-base'>
            Début : <span class='font-semibold'>{{ $results->start_date->format("j/m/Y") }}</span>
        </div>
    @endif

    @if ($results->end_date)
        <div class='text-base'>
            Fin : <span class='font-semibold'>{{ $results->end_date->format("j/m/Y") }}</span>
        </div>
    @endif

    @if ($results->url)
        <div class='text-base'>
            URL : <a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href="{{ $results->url }}">{{ $results->url }}</a>
        </div>
    @endif

    <div class='text-base'>
        Evènement {{ ($results->is_confirmed ? "confirmé" : "non encore confirmé") }}
    </div>
    <div class='text-base'>
        Scope : {!! ($results->is_full_scope ? "Imaginaire <b>et</b> littérature" : "Imaginaire <b>ou</b> littérature") !!}
    </div>

    <div class='text-base my-4 p-2 border border-yellow-500 bg-yellow-50'>
        {!! $results->information !!}
    </div>
</div>