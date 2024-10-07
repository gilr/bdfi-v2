
    {{-- Solution 1 : On ne traite que les titres de niveau "parent" --}}
    {{-- Pour les noms "références" --}}
    @if ($title->parent_id == 0)
        <div class='ml-2 md:ml-8'>
            <a class='text-green-800 border-b border-dotted border-purple-700 hover:text-purple-700' href='/textes/{{ $title->slug }}'>{{ $title->name }}</a>{{ $title->title_vo !== "" ? " ($title->copyright, $title->title_vo)" : "" }}, {{ StrDateformat($title->copyright_fr) }}, {{ $title->type->getLabel() }}
            @if ((auth()->user() && auth()->user()->hasGuestRole()))
                {!! displayAuthorBiblio (0, $results, $title) !!}
            @endif
            {!! displayAuthorBiblio (1, $results, $title) !!}
{{--
            @forelse($title->authors as $author)
                <a class='text-red-800 border-b border-dotted border-purple-700 hover:text-purple-700' href='/auteurs/{{ $author->slug }}'>{{ $author->fullname }}</a>
            @empty
                <span class='font-semibold text-red-500'> Non crédité ou inconnu</span>
            @endforelse
--}}
        </div>
        <ul class='pub list-disc'>
            @if ($title->is_fullserial)
                <li class='ml-5 md:ml-24'>
                    {{ $title->serial_info }}
                </li>
            @endif
            @foreach ($title->publications as $publication)
                <li class='ml-5 md:ml-24'>
                    <i>in</i> <a class='text-blue-800 text-black border-b border-dotted border-black hover:text-purple-700' href='/ouvrages/{{ $publication->slug }}'> {{ $publication->name }}</a>, {{ $publication->publisher->name }} {{ count($publication->collections) ? ", " . $publication->collections[0]->name : "" }} {{ $publication->is_hardcover ? " (relié)" : "" }},  {{ StrDateformat($publication->approximate_parution) }}
                </li>
            @endforeach
        </ul>
        @if(count($title->variants) !== 0)
            @foreach ($title->variants as $variant)
                <div class='ml-5 md:ml-16'>
                    @if($variant->variant_type !== 'traduction')
                        @if($variant->variant_type === 'trad+titre')
                            <i>Retraduit sous</i>
                        @else
                            <i>Sous</i>
                        @endif
                        <a class='text-green-800 border-b border-dotted border-purple-700 hover:text-purple-700' href='/textes/{{ $variant->slug }}'>{{ $variant->name }}</a>, {{ StrDateformat($variant->copyright_fr) }} -
                        @if ((auth()->user() && auth()->user()->hasGuestRole()))
                            {!! displayAuthorBiblio (0, $results, $variant) !!}
                        @endif
                        {!! displayAuthorBiblio (1, $results, $variant) !!}
{{--
                        @forelse($variant->authors as $author)
                            <a class='text-red-800 border-b border-dotted border-purple-700 hover:text-purple-700' href='/auteurs/{{ $author->slug }}'>{{ $author->fullname }}</a>
                        @empty
                            <span class='font-semibold text-red-500'> Non crédité ou inconnu</span>
                        @endforelse
--}}
                    @else
                        <a class='text-greeb-800 border-b border-dotted border-purple-700 hover:text-purple-700' href='/textes/{{ $variant->slug }}'>{{ $variant->name }}</a>, nouvelle traduction, {{ StrDateformat($variant->copyright_fr) }} -
                        @if ((auth()->user() && auth()->user()->hasGuestRole()))
                            {!! displayAuthorBiblio (0, $results, $variant) !!}
                        @endif
                        {!! displayAuthorBiblio (1, $results, $variant) !!}
{{--
                        @forelse($variant->authors as $author)
                            <a class='text-red-800 border-b border-dotted border-purple-700 hover:text-purple-700' href='/auteurs/{{ $author->slug }}'>{{ $author->fullname }}</a>
                        @empty
                            <span class='font-semibold text-red-500'> Non crédité ou inconnu</span>
                        @endforelse
--}}
                    @endif
                </div>
                <ul class='pub list-disc'>
                    @foreach ($variant->publications as $publication)
                        <li class='ml-8 md:ml-24'>
                            <i>in</i> <a class='text-blue-800 text-black border-b border-dotted border-black hover:text-purple-700' href='/ouvrages/{{ $publication->slug }}'> {{ $publication->name }}</a>, {{ $publication->publisher->name }} {{ count($publication->collections) ? ", " . $publication->collections[0]->name : "" }} {{ $publication->is_hardcover ? " (relié)" : "" }},  {{ StrDateformat($publication->approximate_parution) }}
                        </li>
                    @endforeach
                </ul>
            @endforeach
        @endif
    @endif

{{-- On retire pour l'instant, si pseudonyme on met tous les titres ou leur parent. --}}

{{-- Solution 2 : On traite tous les titres sans gérer les variants --}}
{{-- Pour les noms "non références" --}}
{{--

@if ($type == 'normal')
@else

    <div class='ml-2 md:ml-8'>
        <a class='text-blue-800 border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/textes/{{ $title->slug }}'>{{ $title->name }}</a>
        ({{ $title->copyright }}{{ $title->title_vo !== "" ? ", $title->title_vo)" : ")" }}, {{ $title->type->getLabel() }}
    </div>
    <ul class="pub list-disc">
         @if ($title->is_fullserial)
             <li class='ml-5 md:ml-16'>
                 {{ $title->serial_info }}
             </li>
         @endif
         @foreach ($title->publications as $publication)
             <li class='ml-5 md:ml-16'>
                 <i>in</i> <a class='text-black border-b border-dotted border-black hover:text-purple-700 focus:text-purple-900' href='/ouvrages/{{ $publication->slug }}'> {{ $publication->name }}</a>, {{ $publication->publisher->name }} {{ count($publication->collections) ? ", " . $publication->collections[0]->name : "" }} {{ $publication->is_hardcover ? " (relié)" : "" }},  {{ StrDateformat($publication->approximate_parution) }}
             </li>
         @endforeach
    </ul>
@endif
--}}