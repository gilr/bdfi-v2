@extends('front.layout')

@section('content')

    <x-front.menu-index tab='welcome' zone='{{ $area }}' digit='{{ $digit }}'/>
    <div class='text-sm -my-1 p-0 bold self-center hidden sm:inline'>
        La barre d'initiales ci-dessus donne accès aux index paginés.
    </div>
    @includeIf('front.'. $area. '._submenu')

    <div class='text-2xl my-2 md:mt-8 bold self-center'>
        <img sb-icon src="/img/{{ $icon }}" class="w-4 md:w-8 inline" title="{{ $title }}"/> <b>{{ $title }}</b>
    </div>

    @includeIf('front.'. $area. '._message')

    <div class='text-lg mx-2 my-1 md:my-2 bold self-center'>
        <form action="{{ route($area . '.search') }}" method="GET">
            Recherche :
            <input class="px-2 border-2 border-green-500 rounded" type="text" name="s" placeholder='votre recherche...' required/>
            <input class="appearance-none checked:bg-lime-300 px-2.5 py-1.5 border-2 border-green-500 rounded" id='large' name="m" type="checkbox"/><label class='border-b border-green-500 pl-1' for='large'>Recherche élargie</label>
            <button class="px-2 bg-emerald-200 border-2 border-green-500 rounded" type="submit">Go</button>
        </form>
    </div>

    <div class='text-base my-4 bold self-center'>
        Les dernières fiches modifiées :
    </div>

    <div class='grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 text-base px-2 mx-2 md:mx-12 lg:mx-24 self-center sm:w-10/12'>
        @foreach($results as $result)
            <div class='hover:bg-orange-100 border-b hover:border-purple-600'>
                <a class='sm:p-0.5 md:px-0.5' title='{{ $result->full_name ?: $result->name }}' href='/{{ $area }}/{{ $result->id }}'>{{ $result->full_name ? Str::limit($result->full_name, 50) : Str::limit($result->name, 50) }}</a>
            </div>
        @endforeach
    </div>

    <div class='text-2xl pt-12 bold self-center'>
        <b>Infos site test & membres</b>
    </div>
    <div class='text-base my-2 mx-2 md:mx-20 lg:mx-60 bold self-center'>
        En cliquant sur un item des index, on accède à une page très simplifiée (données minimales).
        Si vous êtes identifié avec des droits suffisants (i.e. non simple "user"), un accès direct à la fiche permet de  modifier ou corriger rapidement.
    </div>
    <div class='text-base my-2 mx-2 md:mx-20 lg:mx-60 bold self-center'>
        En modifiant une fiche dans le domaine administration, la page de cette zone sera mise à jour (il faut rafraichir la page).
    </div>
    <div class='text-base my-2 mx-2 md:mx-20 lg:mx-60 bold self-center bg-red-200 p-2 shadow-lg'>
        Rappel : pas d' inquiétude, il  s'agit bien de données de test (copie de test de la base de donnée BDFI).
    </div>

@endsection
