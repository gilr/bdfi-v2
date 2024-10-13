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

    @if (($results->type == App\Enums\PublisherType::AUTOEDITEUR) || ($results->type == App\Enums\PublisherType::COMPTE_AUTEUR))
        <div class='text-base'>
            Type : <span class='font-semibold'>{{ $results->type->getLabel() }}</span>
        </div>
    @endif

    @if ($results->country_id)
        <div class='text-base'>
            Pays : <span class='font-semibold'>{{ $results->country->name }}</span>
        </div>
    @endif

    @if ((auth()->user() && auth()->user()->hasGuestRole()) || ($results->year_start))
        <div class='text-base'>
            Création : <span class='font-semibold'>{{ StrDateformat($results->year_start) }}</span>
        </div>
    @endif

    @if ((auth()->user() && auth()->user()->hasGuestRole()) || ($results->year_end))
        <div class='text-base'>
            Arrêt : <span class='font-semibold'>{{ StrDateformat($results->year_end) }}</span>
        </div>
    @endif

    @if ((auth()->user() && auth()->user()->hasGuestRole()) || ($results->address))
        <div class='text-base'>
            Dernière adresse connue : {{ $results->address }}
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
</div>
</div>

<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>

    @if((count($results->collections)) || (count($results->publicationsWithoutCollection)))
        <hr class="mx-24 my-2 border-dotted border-purple-800"/>
        <div class='text-base'>
            <span class='font-semibold '>Accès aux publications par collection ou ensemble d'ouvrages :</span>
        </div>
    @endif

    @if(count($results->collections) > 20)
        <div class='grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 text-base px-2 mx-2 md:mx-12 lg:mx-24 self-center sm:w-10/12'>
            @foreach ($results->collections as $collection)
                @if (!$collection->parent_id)
                    <div class='ml-2 md:ml-8'>
                        <x-front.display-icon-v2beta-if value='{{ $collection->is_in_v2beta }}' />
                        <a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/collections/{{ $collection->slug }}'>{{ $collection->name }} </a>
                    </div>
                @endif
            @endforeach
            @if(count($results->collections2))
            @foreach ($results->collections2 as $collection)
                @if (!$collection->parent_id)
                    <div class='ml-2 md:ml-8'>
                        <x-front.display-icon-v2beta-if value='{{ $collection->is_in_v2beta }}' />
                        <a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/collections/{{ $collection->slug }}'>{{ $collection->name }} </a>
                    </div>
                @endif
            @endforeach
                @endif
            @if(count($results->collections3))
            @foreach ($results->collections3 as $collection)
                @if (!$collection->parent_id)
                    <div class='ml-2 md:ml-8'>
                        <x-front.display-icon-v2beta-if value='{{ $collection->is_in_v2beta }}' />
                        <a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/collections/{{ $collection->slug }}'>{{ $collection->name }} </a>
                    </div>
                @endif
            @endforeach
                @endif
            @if(count($results->publicationsWithoutCollection))
                <div class='ml-2 md:ml-8'>
                    <a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/editeurs/{{ $results->slug }}/hc'><i> Ouvrages hors collections et groupes</i></a>
                </div>
            @endif
        </div>
    @else
        @if(count($results->collections))
            @foreach ($results->collections as $collection)
                @if (!$collection->parent_id)
                    <div class='ml-2 md:ml-8'>
                        <x-front.display-icon-v2beta-if value='{{ $collection->is_in_v2beta }}' />
                        <a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/collections/{{ $collection->slug }}'>{{ $collection->name }} </a>
                    </div>
                @endif
            @endforeach
        @endif
        @if(count($results->collections2))
            @foreach ($results->collections2 as $collection)
                @if (!$collection->parent_id)
                    <div class='ml-2 md:ml-8'>
                        <x-front.display-icon-v2beta-if value='{{ $collection->is_in_v2beta }}' />
                        <a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/collections/{{ $collection->slug }}'>{{ $collection->name }} </a>
                    </div>
                @endif
            @endforeach
        @endif
        @if(count($results->collections3))
            @foreach ($results->collections3 as $collection)
                @if (!$collection->parent_id)
                    <div class='ml-2 md:ml-8'>
                        <x-front.display-icon-v2beta-if value='{{ $collection->is_in_v2beta }}' />
                        <a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/collections/{{ $collection->slug }}'>{{ $collection->name }} </a>
                   </div>
                @endif
            @endforeach
        @endif

        @if(count($results->publicationsWithoutCollection))
            <div class='ml-2 md:ml-8'>
                <a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/editeurs/{{ $results->slug }}/hc'><i> Ouvrages hors collections et groupes</i></a>
            </div>
        @endif
    @endif

    @if(count($publications))
        <div class='text-base pt-4'>
            @if(count($results->publications) < 25)
                <span class='font-semibold'>Les ouvrages de l'éditeur :</span>
                <div class="flex flex-wrap">
                    @foreach ($results->publications as $publication)
                        <!-- zone couverture -->
                        <a class='m-auto p-1 lg:p-2' href='/ouvrages/{{ $publication->slug }}'><img class='m-auto p-1 lg:p-2 border border-purple-800' src="https://www.bdfi.info/vignettes/{{ InitialeCouv($publication->cover_front) }}/v_{{ $publication->cover_front }}.jpg" alt="couv" title="Couverture {{ $publication->name }}"></a>
                    @endforeach
                </div>
            @else
                <span class='font-semibold'>Quelques publications de l'éditeur :</span>
                <div class="flex flex-wrap">
                    @foreach ($publications as $publication)
                        <!-- zone couverture -->
                        <a class='m-auto p-1 lg:p-2' href='/ouvrages/{{ $publication->slug }}'><img class='m-auto p-1 lg:p-2 border border-purple-800' src="https://www.bdfi.info/vignettes/{{ InitialeCouv($publication->cover_front) }}/v_{{ $publication->cover_front }}.jpg" alt="couv" title="Couverture {{ $publication->name }}"></a>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

</div>
