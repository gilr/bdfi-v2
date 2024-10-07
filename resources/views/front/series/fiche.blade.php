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

    @if(count($results->subseries) != 0)
        <hr class="mx-24 my-2 border-dotted border-purple-800"/>

        <div class='text-base'>
            <span class='font-semibold'>Séries filles :</span>
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
    @endif

</div>
</div>

<?php $pubs=array(); ?>

<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>

    @if(count($results->titles) != 0)
        <hr class="mx-24 my-2 border-dotted border-purple-800"/>

        <div class='text-base'>
            <span class='font-semibold'>Liste des oeuvres attachées à la série {{ ($results->full_name ?: $results->name) }} :</span>
            @foreach ($results->titles as $title)
                @if (($title->parent_id == 0) && (!$title->is_serial))
                    {{-- On ne traite que les titres de niveau "parent" --}}
                    {{-- et on exclue les épisodes --}}
                    @foreach ($title->publications as $publication)
                        @php
                            $pubs[] = array("id" => $publication->id, "slug" => $publication->slug, "cover_front" => $publication->cover_front, "name" => $publication->name);
                        @endphp
                    @endforeach
                    <div class='ml-2 md:ml-8'>
                        @if ($title->pivot->number)
                            {{ StrConvCycleNum($title->pivot->number) }} -
                        @endif
                        <x-front.lien-texte link='/textes/{{ $title->slug }}'>{{ $title->name }}</x-front.lien-texte>
                        ({{ $title->copyright }}{{ $title->title_vo != NULL ? ", $title->title_vo), " . StrDateformat($title->copyright_fr) : ")"}}

                        @if(count($title->authors) != 0)
                        -
                        @foreach($title->authors as $author)
                            @if (!$loop->first)
                                ,
                            @endif
                            <x-front.lien-auteur link='/auteurs/{{ $author->slug }}'>{{ $author->fullname }}</x-front.lien-auteur>
                        @endforeach
                        @endif
                    </div>
                    @if(count($title->variants) !== 0)
                        @foreach ($title->variants as $variant)
                            @foreach ($variant->publications as $publication)
                                @php
                                    $pubs[] = array("id" => $publication->id, "slug" => $publication->slug, "cover_front" => $publication->cover_front, "name" => $publication->name);
                                @endphp
                            @endforeach
                            <div class='ml-5 md:ml-16'>
                                @if ($variant->variant_type !== App\Enums\TitleVariantType::TRAD->value)
{{--
    case PREMIER       = 'premier';
    case VIRTUEL       = 'virtuel';
    case EPISODE       = 'feuilleton';
    case EXTRAIT       = 'extrait';
    case SIGN          = 'signature';
    case TRAD          = 'traduction';
    case TITRE         = 'titre';
    case SIGNTRADTITRE = 'sign+trad+titre';
    case SIGNTRAD      = 'sign+trad';
    case SIGNTITRE     = 'sign+titre';
    case TRADTITRE     = 'trad+titre';
    case INCONNU       = 'inconnu';
        App\Enums\GenreAppartenance::NON s'il y a eu cast
        sinon App\Enums\GenreAppartenance::NON->value
--}}
                                    @if ($variant->variant_type === App\Enums\TitleVariantType::TRADTITRE->value)
                                        <i>Retraduit sous</i>
                                    @else
                                        <i>Sous</i>
                                    @endif
                                    <x-front.lien-texte link='/textes/{{ $variant->slug }}'>{{ $variant->name }}</x-front.lien-texte>,
                                    {{ StrDateformat($variant->copyright_fr) }} -
                                    @forelse($variant->authors as $author)
                                        <x-front.lien-auteur link='/auteurs/{{ $author->slug }}'>{{ $author->fullname }}</x-front.lien-auteur>
                                    @empty
                                        <span class='font-semibold text-red-500'> Non crédité ou inconnu</span>
                                    @endforelse
                                @else
                                    <x-front.lien-texte link='/textes/{{ $variant->slug }}'>{{ $variant->name }}</x-front.lien-texte>,
                                    nouvelle traduction, {{ StrDateformat($variant->copyright_fr) }} -
                                    @forelse($variant->authors as $author)
                                        <x-front.lien-auteur link='/auteurs/{{ $author->slug }}'>{{ $author->fullname }}</x-front.lien-auteur>
                                    @empty
                                        <span class='font-semibold text-red-500'> Non crédité ou inconnu</span>
                                    @endforelse
                                @endif
                            </div>
                        @endforeach
                    @endif
                @endif
            @endforeach

        </div>
    @endif
</div>

<?php
    $series = collect($pubs);
    $unique=$series->unique('id');
?>

<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>

    @if(count($unique) != 0)
        <div class='text-base pt-4'>
            <span class='font-semibold'>Galerie :</span>
            @include ('front.series._gallery', ['pubs' => $unique])
        </div>
    @endif
</div>




