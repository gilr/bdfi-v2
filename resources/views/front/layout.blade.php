<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        Site
        @if (env('APP_TEST') == "true")
            de test
        @endif
        BDFI - {{ env('VERSION') }}
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>

<body class="bg-gray-100">

    <div class="bg-gradient-to-r from-gray-300 to-yellow-900 text-lg text-white px-4 text-center fixed left-0 py-1 bottom-0 right-0 z-40">
        <a class="border-b border-dotted border-red-700 text-red-800 hover:text-purple-700" href="/site/a-propos">A propos</a> - &copy; BDFI 2001-2025 - <a class="border-b border-dotted border-red-700 text-red-800 hover:text-purple-700" href="/site/contact">Contacts</a>
    </div>
    <div class="h-full w-full flex bg-gray-100 pb-8">

        <!-- container -->
        <x-front.menu zone="{{ $area }}"/>

        <div class='flex flex-col w-full'>
            <div class="text-sm p-2 mx-8 hidden sm:flex justify-between">
                <div class="hidden md:inline ">
                  <!-- breadcrumb -->
                    Vous êtes ici :
                    @if (env('APP_TEST') == "true")
                        <span class="text-xs font-bold border-b-2 text-red-500 border-yellow-300">
                            Test {{ env('VERSION') }}
                        </span>
                    @else
                        <span class="text-xs border-b-2 border-yellow-300">
                            {{ env('VERSION') }}
                        </span>
                    @endif
                    {{ ($title == "" ? "" : "→") }}
                    <a class='border-b-2 border-yellow-300 hover:border-purple-400' href="/{{ $area }}">{{ $title }}</a>
                    @if ((isset($subarea)) && ($subarea != ""))
                    → <a class='border-b-2 border-yellow-300 hover:border-purple-400' href="/{{ $area }}/{{ $subarea }}">{{ $subtitle }}</a>
                    @endif
                    {{ ($page == "" ? "" : "→") }}
                    <span class="border-b-2 border-yellow-300">{{ ($page == "" ? "" : $page) }}</span>
                </div>
                <div>
                  <!-- barre d'authent -->
                    <x-front.authent/>
                </div>
            </div>

            <x-front.flashs/>

            @yield('content')
        </div>

    </div>

    @stack('scripts')
</body>

</html>
