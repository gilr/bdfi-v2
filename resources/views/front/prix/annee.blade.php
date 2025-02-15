@extends('front.layout')

@section('content')

    <x-front.menu-prix-annee tab='{{ $annee }}' :annees="$annees"/>

    <div class='text-xl text-purple-800 mt-2 bold self-center pt-2'>
        Récompenses décernées l'année {{ $annee }}
    </div>

    <div class='text-xl text-purple-800 mb-2 bold self-center pb-2'>
          @if ($prev)
              <a class='text-sm border-2 bg-purple-100 px-2 border-purple-700 rounded shadow-md hover:text-purple-700 focus:text-purple-900' href='/prix/annee/{{ $prev }}'>◄ préc.</a>
          @else
              <span class='text-sm text-slate-400 border-2 bg-slate-100 px-2 border-slate-300 rounded shadow-md'>◄ préc.</span>
          @endif
          @if ($next)
                <a class='text-sm border-2 bg-purple-100 px-2 border-purple-700 rounded shadow-md hover:text-purple-700 focus:text-purple-900' href='/prix/annee/{{ $next }}'>suiv. ►</a>
          @else
              <span class='text-sm text-slate-400 border-2 bg-slate-100 px-2 border-slate-300 rounded shadow-md'>suiv. ►</span>
          @endif
    </div>
    @if(count($laureats) > 0)
    <div class='text-base px-2 mx-2 md:mx-40 self-center'>
        @foreach($laureats as $type)
            <div class='font-bold pt-2 pb-1'><a class='hover:bg-yellow-100 border-b hover:border-purple-400 sm:px-0.5 md:px-1' href='/prix/type/{{ $type[0]->type }}'>{{ App\Enums\AwardCategoryType::from($type[0]->type)->GetLabel() }}</a></div>
            @foreach($type as $laureat)
                <div class='pl-2'>
                    {!! awardAuthors($laureat->name, $laureat->author, $laureat->author2, $laureat->author3) !!} -
                    @if ($laureat->titleRef)
                        <x-front.lien-texte link='/textes/{{ $laureat->titleRef->slug }}'>{{ $laureat->title }}</x-front.lien-ouvrage>
                    @else
                        {{ $laureat->title }}
                    @endif
                    {{ $laureat->title == "" ? $laureat->vo_title : ($laureat->vo_title == "" ? "" : "(" . $laureat->vo_title . ")") }} :
                    <a class='hover:bg-yellow-100 border-b hover:border-purple-400 sm:px-0.5 md:px-1' href='/prix/{{ $laureat->award_category->award->name }}'>
                        {{ $laureat->award_category->award->name }}
                    </a>,
                    catégorie
                    <a class='hover:bg-yellow-100 border-b hover:border-purple-400 sm:px-0.5 md:px-1' href='/prix/categorie/{{ $laureat->award_category_id }}'>
                        {{ $laureat->award_category->name }}
                    </a>
                </div>
            @endforeach
        @endforeach
    </div>
    @endif

@endsection