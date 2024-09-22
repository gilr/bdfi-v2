<?php
    $menulist = [
        'auteurs' => [
            'name' => 'Auteurs',
            'zone' => 'auteurs',
            'icon' => 'auteur.png',
            'title' => "Auteurs et pseudonymes"
        ],
        'ouvrages' => [
            'name' => 'Ouvrages',
            'zone' => 'ouvrages',
            'icon' => 'livre.png',
            'title' => "Publications"
        ],
        'retirages' => [
            'name' => 'Retirages',
            'zone' => 'retirages',
            'icon' => 'livre2.png',
            'title' => "Retirages"
        ],
        'textes' => [
            'name' => 'Textes',
            'zone' => 'textes',
            'icon' => 'texte.png',
            'title' => "Textes (titres d'oeuvres)"
        ],
        'cycles' => [
            'name' => 'Cycles',
            'zone' => 'series',
            'icon' => 'series.png',
            'title' => "Cycles et séries"
        ],
        'editeurs' => [
            'name' => 'Editeurs',
            'zone' => 'editeurs',
            'icon' => 'editeurs.png',
            'title' => "Maisons d'édition"
        ],
        'collections' => [
            'name' => 'Collections',
            'zone' => 'collections',
            'icon' => 'collection.png',
            'title' => "Collections et sous-collections"
        ],
        'programmes' => [
            'name' => 'Programme',
            'zone' => 'programme',
            'icon' => 'programme.png',
            'title' => "Programme de publication"
        ],
        'prix' => [
            'name' => 'Prix',
            'zone' => 'prix',
            'icon' => 'prix.png',
            'title' => "Prix"
        ],
        'salons' => [
            'name' => 'Salons',
            'zone' => 'evenements',
            'icon' => 'festival.png',
            'title' => "Salons et festivals"
        ],
        'site' => [
            'name' => 'Le site',
            'zone' => 'site',
            'icon' => 'annonces.png',
            'title' => "Informations du site"
        ],
        'stats' => [
            'name' => 'Chiffres',
            'zone' => 'stats',
            'icon' => 'stats.png',
            'title' => "Statistiques BDFI & imaginaire"
        ],
    ];
?>

<div id="menu" class="hidden sm:block">
    <aside id="sidebar" class="bg-gradient-to-r from-gray-50 to-slate-300 flex flex-col items-center text-gray-700 shadow h-full border-r border-red-800">
        <!-- Side Nav Bar-->
        <ul class="w-full">
            <li class='border-r-4 h-12 flex items-center {{ ($zone == "welcome" ? "bg-purple-200 border-purple-600" : "border-gray-500 hover:bg-yellow-100 hover:border-yellow-500") }}' title="Accueil BDFI">
                <!-- Logo Section -->
                <a class="text-2xl mx-auto p-1 w-28 text-center bold bg-cyan-50 border border-2 border-cyan-400 rounded-full shadow" href="/">
                    <b>B</b><b class="text-cyan-800">DFI</b>
                </a>
            </li>
            <!-- Items Section -->
            @foreach($menulist as $item)
                @if (($item['name'] != "Retirages") || ($item['name'] != "Statistiques") || (auth()->user() && auth()->user()->hasGuestRole()))
                    <li class="pl-1 md:pl-2 border-r-4 {{ ($zone == $item['zone'] ? 'bg-purple-200 border-purple-600' : 'border-gray-500 hover:bg-yellow-100 hover:border-yellow-500') }}">
                        <a href="/{{$item['zone']}}" class="h-12 flex items-center focus:text-yellow-600">
                            @if ((!auth()->user()) || (auth()->user() && auth()->user()->with_icons))
                                <img sb-icon src="/img/{{$item['icon']}}" class="w-7" title="{{$item['title']}}"/>
                            @endif
                            <span class="text-lg pl-2">{{$item['name']}}</span>
                        </a>
                    </li>
                @endif
            @endforeach

            <li class="pl-1 md:pl-2 border-r-4 border-red-800 {{ ($zone == 'forums' ? 'bg-purple-200 border-purple-600' : 'hover:bg-yellow-100 hover:border-yellow-500') }}">
                <a href="https://forums.bdfi.net" class="h-16 flex items-center focus:text-yellow-600" title="Nos forums">
                    @if ((!auth()->user()) || (auth()->user() && auth()->user()->with_icons))
                        <img sb-icon src="/img/forum.png" class="w-7" />
                    @endif
                    <span class="text-lg pl-2">Forums</span>
                </a>
            </li>

        </ul>

        <div class="mt-2 h-32 flex items-center w-full">
            <!-- Action Section (le SVG est un bouton de delog) -->
            <button
                class="h-16 p-4 mx-auto hidden sm:flex justify-center items-center w-full focus:text-yellow-600 bg-yellow-50 border-gray-500 hover:bg-purple-50 focus:outline-none">
                <svg
                    class="h-5 w-5 text-red-700"
                    xmlns="http://www.w3.org/2000/svg"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                <div class="text-xs">bye</div>
            </button>
        </div>

    </aside>
</div>

<label class="block sm:hidden relative z-40 cursor-pointer px-1 py-1" for="mobile-menu">
    <input class="peer/lab hidden" type="checkbox" id="mobile-menu" />
    <img class="border font-black -pl-2 bg-purple-200" id="btn" src="/img/menu-regular-24.png" />
    <div
        class="relative z-50 block bg-black bg-transparent content-[''] before:absolute before:top-[-0.35rem] before:z-50 before:block before:h-full before:w-full before:bg-black before:transition-all before:duration-200 before:ease-out before:content-[''] after:absolute after:right-0 after:bottom-[-0.35rem] after:block after:h-full after:w-full after:bg-black after:transition-all after:duration-200 after:ease-out after:content-['']">
    </div>
    <div class="fixed inset-0 z-40 hidden h-full w-full bg-black/50 backdrop-blur-sm peer-checked/lab:block">
        &nbsp;
    </div>
    <div class="fixed top-0 right-0 z-40 h-full w-full translate-x-full overflow-y-auto overscroll-y-none transition duration-500 peer-checked/lab:translate-x-0">
        <div class="float-right min-h-full w-64 bg-white px-6 pt-12 shadow-2xl">
            <menu>
            <li class='border-r-4 h-12 flex items-center {{ ($zone == "welcome" ? "bg-purple-200 border-purple-600" : "border-gray-500 hover:bg-yellow-100 hover:border-yellow-500") }}' title="Accueil BDFI">
                <!-- Logo Section -->
                <a class="text-2xl mx-auto p-1 text-center w-32 bold bg-cyan-50 border border-2 border-cyan-400 rounded-full shadow" href="/">
                    <b>B</b><b class="text-cyan-800">DFI</b>
                </a>
            </li>
            <!-- Items Section -->
            @foreach($menulist as $item)
                @if (($item['name'] != "Retirages") || ($item['name'] != "Sattistiques") || (auth()->user() && auth()->user()->hasGuestRole()))
                    <li class="pl-1 border-r-4 {{ ($zone == $item['zone'] ? 'bg-purple-200 border-purple-600' : 'border-gray-500 hover:bg-yellow-100 hover:border-yellow-500') }}">
                        <a href="/{{$item['zone']}}" class="h-12 flex items-center focus:text-yellow-600">
                            @if ((!auth()->user()) || (auth()->user() && auth()->user()->with_icons))
                                <img sb-icon src="/img/{{$item['icon']}}" class="w-7" title="{{$item['title']}}"/>
                            @endif
                            <span class="text-lg pl-2">{{$item['name']}}</span>
                        </a>
                    </li>
                @endif
            @endforeach

            <li class="pl-1 border-r-4 border-red-800 {{ ($zone == 'forums' ? 'bg-purple-200 border-purple-600' : 'hover:bg-yellow-100 hover:border-yellow-500') }}">
                <a href="/forums" class="h-16 flex items-center focus:text-yellow-600" title="Nos forums">
                    @if ((!auth()->user()) || (auth()->user() && auth()->user()->with_icons))
                        <img sb-icon src="/img/forum.png" class="w-7" />
                    @endif
                    <span class="text-lg pl-2">Forums</span>
                </a>
            </li>
            </menu>
        </div>
    </div>
</label>

<style>
/* Styles à l'affichage nouvelle page */
#sidebar
{
    width: 144px;
}
#sidebar span {
    color: #000;
    opacity: 1;
}
</style>

