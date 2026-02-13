

<hr class="mx-24 my-2 border-dotted border-purple-800 pt-2 display:block"/>

<style>
    input.swap ~ .pub { display: none; }
    input.swap:checked ~ .pub { display: block; }
</style>


<div class='text-base'>
    <input class='swap mx-4 my-1 accent-purple-200' type="checkbox" unchecked>Afficher les publications<br />
            <span class='font-semibold'>Oeuvres liées à la série {{ ($results->full_name ?: $results->name) }} :</span>
            @foreach ($results->titles as $title)
                @if (($title->parent_id == 0) && (!$title->is_serial))
                    {{-- On ne traite que les titres de niveau "parent" --}}
                    {{-- et on exclue les épisodes --}}

                    <div class='ml-2 md:ml-8'>
                        @if ($title->pivot->number)
                            {{ StrConvCycleNum($title->pivot->number) }} -
                        @endif
                        <x-front.lien-texte link='/textes/{{ $title->slug }}'>{!! $title->name !!}</x-front.lien-texte>
                        ({{ $title->copyright }}{{ $title->title_vo != NULL ? ", $title->title_vo), " . StrDateformat($title->copyright_fr) : ")"}}
                        - <span class='hidden md:inline text-gray-800'>{{ $title->type->getLabel() }}</span>
                        @if(count($title->authors) != 0)
                            de
                            @foreach($title->authors as $author)
                                @if (!$loop->first)
                                    ,
                                @endif
                                <x-front.lien-auteur link='/auteurs/{{ $author->slug }}'>{{ $author->fullname }}</x-front.lien-auteur>
                            @endforeach
                        @endif
                    </div>

                    <ul class='pub list-disc'>
                        @if ($title->is_fullserial)
                            <li class='ml-5 md:ml-24'>
                                {{ $title->serial_info }}
                            </li>
                        @endif
                        @foreach ($title->publications as $publication)
                            <li class='ml-5 md:ml-24'>
                                <i>in</i> <a class='text-blue-800 text-black border-b border-dotted border-black hover:text-purple-700' href='/ouvrages/{{ $publication->slug }}'> {!! $publication->name !!}</a>, {{ $publication->publisher->name }} {{ count($publication->collections) ? ", " . $publication->collections[0]->name : "" }} {{ $publication->is_hardcover ? " (relié)" : "" }},  {{ StrDateformat($publication->approximate_parution) }}
                                @if ($publication->status === App\Enums\PublicationStatus::ANNONCE)
                                    <span class="font-semibold">(publication annoncée)</span>
                                @endif
                                @if ($publication->status === App\Enums\PublicationStatus::ABANDONNE)
                                    <span class="font-semibold">(ouvrage annoncé mais non paru)</span>
                                @endif
                                @if ($publication->status === App\Enums\PublicationStatus::PROPOSE)
                                    <span class="font-semibold">(Sous réserve - référencement en cours de traitement)</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>

                    @if(count($title->variants) !== 0)
                        @foreach ($title->variants as $variant)

                            <div class='ml-5 md:ml-16'>
                                @if ($variant->variant_type !== App\Enums\TitleVariantType::TRAD->value)

                                    @if ($variant->variant_type === App\Enums\TitleVariantType::TRADTITRE->value)
                                        <i>Retraduit sous</i>
                                    @else
                                        <i>Sous</i>
                                    @endif
                                    <x-front.lien-texte link='/textes/{{ $variant->slug }}'>{!! $variant->name !!}</x-front.lien-texte>,
                                    {{ StrDateformat($variant->copyright_fr) }} -
                                    @forelse($variant->authors as $author)
                                        <x-front.lien-auteur link='/auteurs/{{ $author->slug }}'>{{ $author->fullname }}</x-front.lien-auteur>
                                    @empty
                                        <span class='font-semibold text-red-500'> Non crédité ou inconnu</span>
                                    @endforelse
                                @else
                                    <x-front.lien-texte link='/textes/{{ $variant->slug }}'>{!! $variant->name !!}</x-front.lien-texte>,
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
