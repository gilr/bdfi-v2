<div class='grid grid-cols-1 lg:grid-cols-2 gap-0.5 bg-gradient-to-b from-yellow-400 via-pink-500 to-purple-500 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4'>

<div class='bg-gray-100 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>

    <div class='text-base mt-2 font-semibold bg-yellow-50'>
         {{ $results->name }}
    </div>

    @if ($results->alt_names)
        <div class='text-base'>
            Autres formes du titre : <span class='font-semibold'>{{ $results->alt_names }}</span>
        </div>
    @endif

    @if ($results->vo_names)
        <div class='text-base'>
            Formes VO du titre : <span class='font-semibold'>{{ $results->vo_names }}</span>
        </div>
    @endif

    <div class='text-base'>
        @if (count($results->getAuthors()) > 1)
            Contributeurs :
        @else
            Contributeur :
        @endif
        @forelse($results->getAuthors() as $author)
            @if (!$loop->first)
                ,
            @endif
            <x-front.lien-auteur link='/auteurs/{{ $author->slug }}'>{{ $author->fullname }}</x-front.lien-auteur>
        @empty
            <span class='font-semibold text-red-500'> Non crédité ou inconnu</span>
        @endforelse
    </div>

      @if (auth()->user() && auth()->user()->hasGuestRole())
        <div class='text-base'>
            Type de série : <span class='text-blue-900 bg-sky-200 shadow-sm shadow-blue-600 rounded-sm px-1'>{{ $results->type->getLabel() }}</span>
        </div>
    @endif

</div>

<div class='bg-gray-100 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>

    @if ((auth()->user() && auth()->user()->hasGuestRole()) || ($results->information))
        <div class='text-base my-4 p-2 border border-yellow-500 bg-yellow-50'>
            @if ($results->information)
                {!! $results->information !!}
            @else
                <span class='italic'>... pas de description</span>
            @endif
        </div>
    @endif

    @if ($results->parent_id != 0)
        <div class='text-base'>
            Sous-cycle/série de : <span class='font-semibold'><a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/series/{{ $results->parent->slug }}'>{{ $results->parent->name }}</a></span>
            @if ($results->parent->parent_id != 0)
                (lui-même sous <span class='font-semibold'><a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/series/{{ $results->parent->parent->slug }}'>{{ $results->parent->parent->name }}</a>)</span>
            @endif
        </div>
    @endif

<style>
.scroll-container {
    max-height: 300px; /* Hauteur de la zone ascenseur */
    overflow-y: auto;
    border-left: 2px solid #ccc;
    margin: 5px;
    padding: 0 5px;
    direction: rtl; /* Ascenseur à gauche en inversant direction */
    scrollbar-width: thick; /* Firefox uniquement */
}
.scroll-container * {
    direction: ltr; /* ... mais texte dans le bon sens */
}
</style>

    @if(count($results->subseries) != 0)
        <hr class="mx-24 my-2 border-dotted border-purple-800"/>

        <div class='text-base'>
            <span class='font-semibold'>Séries filles :</span>
            <div class='scroll-container'>
            @foreach ($results->subseries as $subserie)
                <div class='ml-2 md:ml-8'>
                    <a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/series/{{ $subserie->slug }}'>
                        {{ $subserie->name }}
                    </a>
                    @if(count($subserie->subseries) != 0)
                        @foreach ($subserie->subseries as $subsub)
                            <div class='ml-2 md:ml-8'>
                                <a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/series/{{ $subsub->slug }}'>
                                {{ $subsub->name }}
                                </a>
                            </div>
                        @endforeach
                    @endif
                </div>
            @endforeach
        </div>
        </div>
    @endif

</div>
</div>

<?php $pubs=array(); ?>

<!--- Liste des ouvrages !-->
@if(count($results->titles) != 0)
    @foreach ($results->titles as $title)
        @if (($title->parent_id == 0) && (!$title->is_serial))
            {{-- On ne traite que les titres de niveau "parent" --}}
            {{-- et on exclue les épisodes --}}
            @foreach ($title->publications as $publication)
                @php
                    $pubs[] = array(
                        "id" => $publication->id,
                        "slug" => $publication->slug,
                        "cover_front" => $publication->cover_front,
                        "name" => $publication->name,
                        "publisher" => isset($publication->publisher) ? $publication->publisher->name : "inconnu",
                        "paru" => $publication->approximate_parution);
                @endphp
            @endforeach
            @if(count($title->variants) !== 0)
                @foreach ($title->variants as $variant)
                    @foreach ($variant->publications as $publication)
                        @php
                            $pubs[] = array(
                                "id" => $publication->id,
                                "slug" => $publication->slug,
                                "cover_front" => $publication->cover_front,
                                "name" => $publication->name,
                                "publisher" => isset($publication->publisher) ? $publication->publisher->name : "inconnu",
                                "paru" => $publication->approximate_parution);
                        @endphp
                    @endforeach
                @endforeach
            @endif
        @endif
    @endforeach
@endif

<?php
    $series = collect($pubs);
    $unique=$series->unique('id');
?>

@if(count($results->titles) < 25)
    <!--- Si moins de 25, affichage à plat !-->

    <div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
        @include ('front.series._liste')
    </div>

    <div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>

        @if(count($unique) != 0)
            <div class='text-base pt-4'>
                <span class='font-semibold'>Galerie :</span>
                @include ('front.series._gallery', ['pubs' => $unique])
            </div>
        @endif
    </div>

@else
    <!--- Sinon, affichage en onglets !-->
    <style>
    input.rad { display: none; }
    input.rad + label { display: inline-block; }

    input.rad ~ .tab {
        display: none;
        overflow: hidden;
        border-top: 1px solid blue;
        padding: 12px;
    }

    #tab1:checked ~ .tab.content1,
    #tab2:checked ~ .tab.content2 { display: block; }

    input.rad + label {
      border: 1px solid #999;
      background: #EEE;
      padding: 4px 12px;
      border-radius: 4px 4px 0 0;
      cursor: pointer;
      position: relative;
      top: 1px;
      width:240px;
    }
    input.rad:checked + label {
      background-color: rgb(233 213 255);
      border-bottom: 1px solid transparent;
      font-weight: 600;
    }

    .tab:after {
      content: "";
      display: table;
      clear: both;
    }
    </style>

    <div class='block pt-2 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4'>
        <input class='rad' type="radio" name="tabs" checked="checked" id="tab1" />
        <label for="tab1">Oeuvres</label>
        <input class='rad' type="radio" name="tabs" id="tab2" />
        <label for="tab2">Galerie</label>
        <div class="tab content1 text-base">
            @include ('front.series._liste')
        </div>
        <div class="tab content2 text-base">
            @include ('front.series._gallery', ['pubs' => $unique])
       </div>
    </div>
@endif



