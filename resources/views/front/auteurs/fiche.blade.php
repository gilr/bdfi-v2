<div class='grid grid-cols-1 lg:grid-cols-2 gap-0 lg:gap-0.5 bg-gradient-to-b from-yellow-400 via-pink-500 to-purple-500 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4'>

    {{--  --> 1. Pas de renvoi : la signature n'est pas attachée à une ou plusieurs références --}}
    {{--  --> 2. Renvoi sur une signature unique --}}
    {{--      3. Renvoi sur deux références --}}
    {{--      4. Renvoi sur plusieurs référebces --}}
    @if (($type == 'normal') || ($type == 'redirect'))
    <div class='bg-gray-100 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>

        <div class='text-base mt-2 bg-yellow-50'>
        @if ($results->first_name)
             <span class='font-semibold'>{{ $results->name }}</span>, {{ sanitizeFirstName($results->first_name) }}
        @else
             <span class='font-semibold'>{{ $results->name }}</span>
        @endif
        </div>

        @if ($results->is_pseudonym)
            <div class='text-base'>
                Pseudonyme
            </div>
        @endif

        @if (($results->legal_name) && ($results->legal_name !== $results->fullName))
            <div class='text-base'>
                Nom légal : <span class="font-semibold">{{ $results->legal_name }}</span>
            </div>
        @endif

        @if ($results->alt_names)
            <div class='text-base'>
                Autres formes du nom : <span class="font-semibold">{{ $results->alt_names }}</span>
            </div>
        @endif

        @if ($type == 'normal')
            <?php $auteur = $results; ?>
        @else
            <?php $auteur = $results->references[0]; ?>
        @endif

        <div class='text-base'>
            <span class='font-semibold'><a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/auteurs/pays/{{ $auteur->country->name }}'>{{ $auteur->country->name }}</a></span>

            @if ($auteur->country2_id !== NULL)
                @if ($auteur->country2->name !== "?")
                    , <span class='font-semibold'><a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/auteurs/pays/{{ $auteur->country2->name }}'>{{ $auteur->country2->name }}</a></span>
                @endif
            @endif
            {!! formatAuthorDates ($auteur->gender, $auteur->birth_date, $auteur->date_death, $auteur->birthplace, $auteur->place_death); !!}
        </div>

        <br />
        @if(($results->referencesCount !== 0) || ($autres_pseudos && count($autres_pseudos) !== 0) || ($results->signaturesCount !== 0))
            <div class='text-base'>Voir également sur BDFI :

            @if($results->referencesCount !== 0)
                @foreach ($results->references as $reference)
                    @if (!$loop->first)
                        ,
                    @endif
                    <a class='text-red-800 border-b border-dotted border-purple-700 hover:text-purple-700' href='/auteurs/{{ $reference->slug }}'>{{ $reference->fullname }}</a>
                @endforeach
            @endif

            @if($autres_pseudos && count($autres_pseudos) !== 0)
                @foreach ($autres_pseudos as $autre)
                    @if (!$loop->first)
                        ,
                    @endif
                    <a class='text-red-800 border-b border-dotted border-purple-700 hover:text-purple-700' href='/auteurs/{{ $autre->signature->slug }}'>{{ $autre->fullName }}</a>
                @endforeach
            @endif

            @if($results->signaturesCount !== 0)
                @foreach ($results->signatures as $signature)
                    @if (!$loop->first)
                        ,
                    @endif
                    <a class='text-red-800 border-b border-dotted border-purple-700 hover:text-purple-700' href='/auteurs/{{ $signature->slug }}'>{{ $signature->fullname }}</a>
                @endforeach
            @endif
            </div>
        @endif
        @if(($results->relationsCount !== 0) && ($results->inverserelationsCount !== 0))
            <div class='text-base'>Relations :
                @foreach ($results->relations as $relation)
                    @if (!$loop->first)
                        ,
                    @endif
                    <a class='text-red-800 border-b border-dotted border-purple-700 hover:text-purple-700' href='/auteurs/{{ $relation->slug }}'>{{ $relation->fullname }}</a> ({{ $relation->pivot->relationship_type->reverse_relationship }})
                @endforeach
                @foreach ($results->inverserelations as $relation)
                    @if (!$loop->first)
                        ,
                    @endif
                    <a class='text-red-800 border-b border-dotted border-purple-700 hover:text-purple-700' href='/auteurs/{{ $relation->slug }}'>{{ $relation->fullname }}</a> ({{ $relation->pivot->relationship_type->relationship }})
                @endforeach
            </div>
        @endif

        @if(count($award_years))
            <div class='text-base pt-2'>
                <span class='font-semibold'>{{ ($results->gender == App\Enums\AuthorGender::F ? "Primée" : "Primé") }} en :</span>
                @foreach ($award_years as $year)
                @if (!$loop->first)
                    ,
                @endif
                    <span class='text-md'>
                        <a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href="/prix/annee/{{ $year['year'] }}">{{ $year['year'] }} </a>
                    </span>
                @endforeach
            </div>
        @endif
    </div>

    <div class='bg-gray-100 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
        <div class='hidden lg:block'>
            @if($auteur->country->name != "?")
                <a href='/auteurs/pays/{{ $results->country->name }}'>
                    <img class='m-auto p-1 lg:p-1.5 border border-purple-800' src="/img/drapeaux/{{ Str::slug(remove_accents($results->country->name),'_') }}.png" />
                </a>
            @else
                <img class='m-auto p-1 lg:p-1.5 border border-purple-800' src="/img/drapeaux/incnat.png" />
            @endif
        </div>

        @if($results->WebsitesCount !== 0)
            <div class='text-base'>
                @if($results->WebsitesCount == 1)
                    Pour de plus amples informations, consulter
                @else
                    Sites web à consulter :
                @endif
                @foreach ($results->websites as $website)
                    @if (!$loop->first)
                        ,
                    @endif
                        <span class='font-semibold'><a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='{{ $website->url }}'>{{ $website->website_type->displayed_text }} </a></span>
                @endforeach
            </div>
        @endif

        @if ($type == 'redirect')
            @if ($results->information)
                <div class='text-base my-4 p-2 border border-yellow-500 bg-yellow-50'>
                    {!! $results->information !!}
                </div>
            @endif
        @endif
        @if ((auth()->user() && auth()->user()->hasGuestRole()) || ($auteur->information))
            <div class='text-base my-4 p-2 border border-yellow-500 bg-yellow-50'>
                @if ($auteur->information)
                    {!! $auteur->information !!}
                @else
                    <span class='italic'>... pas d'informations biographiques</span>
                @endif
            </div>
        @endif

    </div>
    @endif

    {{--      1. Pas de renvoi : la signature n'est pas attachée à une ou plusieurs références --}}
    {{--      2. Renvoi sur une signature unique --}}
    {{--  --> 3. Renvoi sur deux références --}}
    {{--  --> 4. Renvoi sur plusieurs références --}}
    @if (($type == 'collaboration') || ($type == 'multi'))
    <div class='bg-gray-100 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>

        <div class='text-base mt-2 font-semibold bg-yellow-50'>
             <span class='font-semibold'>{{ $results->fullname }}</span>
        </div>

        @if ($results->is_pseudonym)
            <div class='text-base'>
                Pseudonyme collectif
            </div>
        @endif

        @if ($results->legal_name)
            <div class='text-base'>
                Noms légaux : <span class="font-semibold">{{ $results->legal_name }}</span>
            </div>
        @endif

        @if ($results->alt_names)
            <div class='text-base'>
                Autres formes du nom : <span class="font-semibold">{{ $results->alt_names }}</span>
            </div>
        @endif

        <div class='text-base ml-2 my-2'>
            @foreach ($results->references as $auteur)
            <div>
                &rarr; <a class='text-red-800 border-b border-dotted border-purple-700 hover:text-purple-700' href='/auteurs/{{ $auteur->slug }}'>{{ $auteur->fullName }}</a> -
                <span class='font-semibold'>
                    <a class='border-b border-dotted border-purple-700 hover:text-purple-700' href='/auteurs/pays/{{ $auteur->country->name }}'>{{ $auteur->country->name }}</a>
                </span>
            {!! formatAuthorDates ($auteur->gender, $auteur->birth_date, $auteur->date_death, $auteur->birthplace, $auteur->place_death); !!}
            </div>
            @endforeach
        </div>

        @if($autres_pseudos && count($autres_pseudos) !== 0)
            <div class='text-base'>Voir également sur BDFI :
                @foreach ($autres_pseudos as $autre)
                    @if (!$loop->first)
                        ,
                    @endif
                    <a class='text-red-800 border-b border-dotted border-purple-700 hover:text-purple-700' href='/auteurs/{{ $autre->signature->slug }}'>{{ $autre->fullName }}</a></span>
                @endforeach
            </div>
        @endif

    </div>

    <div class='bg-gray-100 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>

        <div class='text-base my-4 p-2 border border-yellow-500 bg-yellow-50'>
            @if ($type == 'multi')
                @if ($results->information)
                    {!! $results->information !!}
                @endif
            @endif
            @if ($type == 'collaboration')
              @if ($results->references[0]->information)
                    <span class='font-semibold'>{!! $results->references[0]->fullName !!}</span> : {!! $results->references[0]->information !!} <br />
                @endif
                @if ($results->references[1]->information)
                    <span class='font-semibold'>{!! $results->references[1]->fullName !!}</span> : {!! $results->references[1]->information !!}
                @endif
            @endif
        </div>
    </div>

    @endif

</div>

<hr class="mx-24 my-2 border-dotted border-purple-800 pt-2 display:block"/>

<?php $laureats=array(); ?>
@if(count($results->winners) !== 0)
    @foreach ($results->winners as $laureat)
        <?php $laureats[] = array("year" => $laureat->year, "award_name" => $laureat->award_category->award->name, "categorie_id" => $laureat->award_category->id, "categorie_name" => $laureat->award_category->name, "title" => $laureat->title, "vo_title" => $laureat->vo_title); ?>
    @endforeach
@endif
@if(count($results->winners2) !== 0)
    @foreach ($results->winners2 as $laureat)
        @php
            $laureats[] = array("year" => $laureat->year, "award_name" => $laureat->award_category->award->name, "categorie_id" => $laureat->award_category->id, "categorie_name" => $laureat->award_category->name, "title" => $laureat->title, "vo_title" => $laureat->vo_title);
        @endphp
    @endforeach
@endif
@if(count($results->winners3) !== 0)
    @foreach ($results->winners3 as $laureat)
        @php
            $laureats[] = array("year" => $laureat->year, "award_name" => $laureat->award_category->award->name, "categorie_id" => $laureat->award_category->id, "categorie_name" => $laureat->award_category->name, "title" => $laureat->title, "vo_title" => $laureat->vo_title);
        @endphp
    @endforeach
@endif
<?php asort($laureats); ?>

@if(count($results->titles) + count($laureats) < 25)
<!--- Si moins de 25, affichage à plat !-->

<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
    @include ('front.auteurs._biblio')
</div>

<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
    @if(count($results->publications) !== 0)
        <div class='text-base pt-4'>
            <span class='font-semibold'>Galerie :</span>
            @include ('front.auteurs._gallery')
        </div>
    @endif
</div>
<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
    @if($laureats && (count($laureats) !== 0))
        <div class='text-base pt-2'>
            <span class='font-semibold'>Prix décernés :</span>
            @include ('front.auteurs._prix')
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
    @if($laureats && (count($laureats) !== 0))
        #tab3:checked ~ .tab.content3 { display: block; }
    @endif

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
        <label for="tab1">Bibliographie</label>
        <input class='rad' type="radio" name="tabs" id="tab2" />
        <label for="tab2">Galerie</label>
        @if($laureats && (count($laureats) !== 0))
            <input class='rad' type="radio" name="tabs" id="tab3" />
            <label for="tab3">Récompenses</label>
        @endif
        <div class="tab content1 text-base">
            @include ('front.auteurs._biblio')
        </div>
        <div class="tab content2 text-base">
            @include ('front.auteurs._gallery')
       </div>
        @if($laureats && (count($laureats) !== 0))
            <div class="tab content3 text-base">
                @include ('front.auteurs._prix')
            </div>
        @endif
    </div>
@endif



