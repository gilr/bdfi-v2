            @foreach ($results->titles as $title)
                @if (($title->parent_id == 0) && (!$title->is_serial))
                    {{-- On ne traite que les titres de niveau "parent" --}}
                    {{-- et on exclue les épisodes --}}
                    @foreach ($title->publications as $publication)
                        @php
                            $pubs[] = array("id" => $publication->id, "cover_front" => $publication->cover_front, "name" => $publication->name);
                        @endphp
                    @endforeach
                    <div class='ml-2 md:ml-8'>
                        @if ($title->pivot->number)
                            {{ StrConvCycleNum($title->pivot->number) }} -
                        @endif
                        <a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/textes/{{ $title->slug }}'>{{ $title->name }}</a>
                        ({{ $title->copyright }}{{ $title->title_vo != NULL ? ", $title->title_vo), " . StrDateformat($title->copyright_fr) : ")"}}

                        @if(count($title->authors) != 0)
                        -
                        @foreach($title->authors as $author)
                            @if (!$loop->first)
                                ,
                            @endif
                            <a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/auteurs/{{ $author->slug }}'>{{ $author->fullname }} </a>
                        @endforeach
                        @endif
                    </div>
                    @if(count($title->variants) !== 0)
                        @foreach ($title->variants as $variant)
                            @foreach ($variant->publications as $publication)
                                @php
                                    $pubs[] = array("id" => $publication->id, "cover_front" => $publication->cover_front, "name" => $publication->name);
                                @endphp
                            @endforeach
                            <div class='ml-5 md:ml-16'>
                                @if($variant->variant_type !== 'traduction')
                                    @if($variant->variant_type === 'trad+titre')
                                        <i>Retraduit sous</i>
                                    @else
                                        <i>Sous</i>
                                    @endif
                                    <a class='text-blue-800 border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/textes/{{ $variant->slug }}'>{{ $variant->name }}, {{ StrDateformat($variant->copyright_fr) }}</a> -
                                    @forelse($variant->authors as $author)
                                        <a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/auteurs/{{ $author->slug }}'>{{ $author->fullname }} </a>
                                    @empty
                                        <span class='font-semibold text-red-500'> Non crédité ou inconnu</span>
                                    @endforelse
                                @else
                                    <a class='text-blue-800 border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/textes/{{ $variant->slug }}'>{{ $variant->name }}</a>, nouvelle traduction, {{ StrDateformat($variant->copyright_fr) }} -
                                    @forelse($variant->authors as $author)
                                        <a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/auteurs/{{ $author->slug }}'>{{ $author->fullname }} </a>
                                    @empty
                                        <span class='font-semibold text-red-500'> Non crédité ou inconnu</span>
                                    @endforelse
                                @endif
                            </div>
                        @endforeach
                    @endif
                @endif
            @endforeach
