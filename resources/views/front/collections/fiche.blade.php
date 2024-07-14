@auth
@php
    $suivie = auth()->user() && (auth()->user()->statusCollection($results->id)) && (auth()->user()->statusCollection($results->id) != 'cachee');
@endphp
<span class='text-lg mb-8 self-center'>
    @if ($suivie)
        <span class='bg-green-200 shadow-sm shadow-gray-400 rounded-sm px-1'>Collection suivie,
    {{ auth()->user()->statusCollection($results->id) }} (<x-admin.link lien='/user/gestion-biblio'>&rarr; Gestion</x-admin.link>)</span>
    @endif
@endauth
</span>


<div class='grid grid-cols-1 lg:grid-cols-2 gap-1 bg-gradient-to-b from-yellow-400 via-pink-500 to-purple-500 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4'>
 <div class='bg-gray-100 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
    <div class='text-base mt-2 font-semibold bg-yellow-50'>
         {{ $results->name }}
    </div>

    @if ($results->alt_names)
        <div class='text-base'>
            Autres formes du nom : <span class='font-semibold'>{{ $results->alt_names }}</span>
        </div>
    @endif

    @if ($results->type != App\Enums\CollectionType::COLLECTION)
        <div class='text-base'>
            Type : <span class='font-semibold'>{{ $results->type->getLabel() }}</span>
        </div>
    @endif

    @if ($results->periodicity != App\Enums\CollectionPeriodicity::NA)
        <div class='text-base'>
            Périodicité : <span class='font-semibold'>{{ $results->periodicity->getLabel() }}</span>
        </div>
    @endif

    <div class='text-base'>
        Nombre de publications : <span class='font-semibold'>{{ count($results->publications) }}</span>
    </div>

    @if ($results->publisher_id)
        <div class='text-base'>
            Editeur  : <span class='font-semibold'><a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/editeurs/{{ $results->publisher_id }}'>{{ $results->publisher->name }}</a></span>
            @if ($results->publisher2_id)
                , <span class='font-semibold'><a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/editeurs/{{ $results->publisher2_id }}'>{{ $results->publisher2->name }}</a></span>
            @endif
            @if ($results->publisher3_id)
                , <span class='font-semibold'><a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/editeurs/{{ $results->publisher3_id }}'>{{ $results->publisher3->name }}</a></span>
            @endif
        </div>
    @endif

    <div class='text-base'>
        Support : <span class='font-semibold'>{{ $results->support->getLabel() }}</span>
    </div>

    <div class='text-base'>
        Création : <span class='font-semibold'>{{ StrDateformat($results->year_start) }}</span>
    </div>

    @if ((auth()->user() && auth()->user()->hasGuestRole()) || ($results->year_end))
        <div class='text-base'>
            Arrêt : <span class='font-semibold'>{{ StrDateformat($results->year_end) }}</span>
        </div>
    @endif

    @if ((auth()->user() && auth()->user()->hasGuestRole()) || ($results->format))
        <div class='text-base'>
            @if ($results->format)
                Format : <span class='font-semibold'>{{ $results->format->getLabel() }}</span>
            @else
                Format : <span class='font-semibold'>?</span>
            @endif
        </div>
    @endif

    @if ((auth()->user() && auth()->user()->hasGuestRole()) || ($results->dimensions))
        <div class='text-base'>
            Dimensions : <span class='font-semibold'>{{ $results->dimensions }}</span>
        </div>
    @endif

    @if ((auth()->user() && auth()->user()->hasGuestRole()) || ($results->cible))
        <div class='text-base'>
            @if ($results->cible)
                Cible (âge) : <span class='font-semibold'>{{ $results->cible->getLabel() }}</span>
            @else
                Cible (âge) : <span class='font-semibold'>?</span>
            @endif
        </div>
    @endif

    @if ((auth()->user() && auth()->user()->hasGuestRole()) || ($results->genre))
        <div class='text-base'>
            @if ($results->genre)
                Genre : <span class='font-semibold'>{{ $results->genre->getLabel() }}</span>
            @else
                Genre : <span class='font-semibold'>?</span>
            @endif
        </div>
    @endif

    @if ($results->forum_topic_id != 0)
        <div class='text-base'>
            Consulter la page consacrée sur nos <span class='font-semibold'><a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='https://forums.bdfi.net/viewtopic.php?id={{ $results->forum_topic_id }}'>forums</a></span>
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
            Collection parente : <span class='font-semibold'><a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/collections/{{ $results->parent_id }}'>{{ $results->parent->name }} </a></span>
        </div>
        <div class='text-base'>
            Sous-collections, sous-ensembles :
        </div>

        <div class='text-base px-2 mx-2 md:mx-10 self-center'>
            @foreach($results->parent->subcollections as $subcollection)
                @if ($subcollection->id != $results->id)
                    <div class='hover:bg-yellow-100 border-b hover:border-purple-400'><a class='sm:p-0.5 md:px-0.5' href='/collections/{{ $subcollection->id }}'> {{ $subcollection->shortname }} </a></div>
                @else
                    <div class='bg-purple-200 border-b border-purple-600 sm:p-0.5 md:px-0.5'> {{ $results->shortname }} </div>
                @endif
            @endforeach
        </div>

    @endif

    @if (count($results->subcollections) != 0)
        <div class='text-base'>
            <span class='font-semibold'>Sous-collection, sous-ensembles :</span>
            @foreach ($results->subcollections as $subcollection)
                <div class='ml-2 md:ml-8'>
                    <a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/collections/{{ $subcollection->id }}'>{{ $subcollection->name }} </a>
                </div>
            @endforeach
        </div>
    @endif

</div>
</div>


@if ((count($results->publications) < 21) && (!isset($results->article)))
    <div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
        @if (count($results->publications) != 0)
            <hr class="mx-24 my-2 border-dotted border-purple-800"/>

            <div class='text-base'>
                <span class='font-semibold'>Liste des publications :</span>
                @include ('front.collections._publications')
            </div>
            <div class='text-base pt-4'>
                <span class='font-semibold'>Galerie :</span>
                @include ('front.collections._gallery')
            </div>
        @endif
    </div>

@else

    <style>
    input.rad { display: none; }
    input.rad + label { display: inline-block; }

    input.rad ~ .tab {
        display: none;
        overflow: hidden;
        border-top: 1px solid blue;
        padding: 12px;
    }

    input#tab1, input#tab2, input#tab3 { display: none; }

    #tab1:checked ~ .tab.content1,
    #tab2:checked ~ .tab.content2 { display: block; }
    #tab3:checked ~ .tab.content3 { display: block; }

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

    .article { padding: 0 20px; margin:0; }
    .article p { display: block; margin:0 10px; padding:0 10px; }
    .article img { display: inline; }
    .article ul { margin:0 10px; padding:0 10px; list-style-position:outside; list-style-type: disc; }
    .article ol { margin:0 10px; padding:0 10px; list-style-position:outside; list-style-type: Upper-greek ; }


    </style>

    <hr class="mx-24 my-2 border-dotted border-purple-800 pt-2 display:block"/>

    <div class='block pt-2 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4'>
        <input class='rad' type="radio" name="tabs" checked="checked" id="tab1" />
        <label for="tab1">Liste des publications</label>
        <input class='rad' type="radio" name="tabs" id="tab2" />
        <label for="tab2">Galerie</label>
        @if (isset($results->article))
            <input class="rad" type="radio" name="tabs" id="tab3" />
            <label for="tab3">Article</label>
        @endif

        <div class="tab content1 text-base">
            @include ('front.collections._publications')
        </div>

        <div class="tab content2 text-base">
            @include ('front.collections._gallery')
        </div>

        @if (isset($results->article))
            <div class="article tab content3 text-base border border-yellow-500 bg-yellow-50 flex">
                {!! $results->article->content !!}
            </div>
        @endif
    </div>
@endif
