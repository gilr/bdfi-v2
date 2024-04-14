<style>
input.swap ~ .pub { display: none; }
input.swap:checked ~ .pub { display: block; }
</style>

@php
    $novels = $bibliofull->Novels();
    $collections = $bibliofull->Collections();
    $shorts = $bibliofull->Shorts();
    $others = $bibliofull->Others();
    $nonfictions = $bibliofull->NonFictions();
@endphp

<div class='text-base'>
<input class='swap mx-4 my-1 accent-purple-200' type="checkbox" unchecked>Afficher les publications<br />

    @if (!$novels->isEmpty())
        <span class='font-semibold'>Romans et courts romans :</span>
        @foreach ($novels as $title)
            @include ('front.auteurs._titre')
        @endforeach
    @endif

    @if (!$collections->isEmpty())
        <span class='font-semibold'>Recueils et anthologies :</span>
        @foreach ($collections as $title)
            @include ('front.auteurs._titre')
        @endforeach
    @endif

    @if (!$shorts->isEmpty())
        <span class='font-semibold'>Fictions courtes :</span>
        @foreach ($shorts as $title)
            @include ('front.auteurs._titre')
        @endforeach
    @endif

    @if (!$others->isEmpty())
        <span class='font-semibold'>Autres :</span>
        @foreach ($others as $title)
            @include ('front.auteurs._titre')
        @endforeach
    @endif

    @if (!$nonfictions->isEmpty())
        <span class='font-semibold'>Textes non fictions :</span>
        @foreach ($nonfictions as $title)
            @include ('front.auteurs._titre')
        @endforeach
    @endif

    @if ($results->writtenDocuments->count() > 0)
        <div class="mt-2">
            <span class='font-semibold'>Documents de l'auteur stockés sur BDFI :</span>
            @foreach ($results->writtenDocuments as $doc)
                <a class="block underline ml-2 md:ml-8" href="/{{ $doc->file }}" target="_blank">{{ $doc->name }}</a>
            @endforeach
        </div>
    @endif

    @if ($results->aboutDocument)
        <div class="mt-2">
            <span class='font-semibold'>Document concernant l'auteur stocké sur BDFI :</span>
            <a class="block underline ml-2 md:ml-8" href="/{{ $results->aboutDocument->file }}" target="_blank">{{ $results->aboutDocument->name }}</a>
        </div>
    @endif
</div>
