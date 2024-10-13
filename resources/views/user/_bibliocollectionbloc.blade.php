
@foreach ($collection->publications as $publication)
    <div class='m-1 text-sm'>
        @if (auth()->user()->statusPublication($publication->id))
            <x-admin.display-icon-pub-owned />
            {{ html()->form('POST', '/user/retirer-publication')->class("inline-block")->open() }}
            {{ html()->hidden("pub", $publication->id) }}
            {{ html()->submit($text = "-")->class("font-mono text-xs bg-red-200 font-semibold border border-red-800 rounded px-1 shadow-md m-0 shadow-red-800/40") }}
            {{ html()->form()->close() }}
        @else
            <x-admin.display-icon-pub-missing />
            {{ html()->form('POST', '/user/ajouter-publication')->class("inline-block")->open() }}
            {{ html()->hidden($name = "pub", $publication->id) }}
            {{ html()->submit($text = "+")->class("font-mono text-xs bg-green-200 font-semibold border border-green-800 rounded px-1 shadow-md m-0 shadow-green-800/40") }}
            {{ html()->form()->close() }}
        @endif

        @if($publication->pivot->number)
            {{ $publication->pivot->number }}.
        @endif
        <x-front.lien-ouvrage link='/ouvrages/{{ $publication->id }}'>{{ $publication->name }}</x-front.lien-ouvrage>,

        @if(count($publication->authors) > 0)
            @foreach($publication->authors as $author)
                @if (!$loop->first)
                    ,
                @endif
                <x-front.lien-auteur link='/auteurs/{{ $author->id }}'>{{ $author->name }}</x-front.lien-auteur>
            @endforeach
        @endif
    </div>
@endforeach


