<x-app-layout>

<!-- Page d'accueil user non administrateur (jetstream) -->
<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Gestion de votre compte BDFI') }}
    </h2>
</x-slot>

<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="px-2 sm:p-4 bg-white border-b border-gray-200">
                <div>
                    <x-utilisateur-logo />
                </div>

                <div class="mt-4 text-lg">
                    Bonjour <span class='font-semibold italic'>{{ Auth::user()->name }}</span>. Cette zone offre :
                    <ul class="hidden sm:block list-inside list-disc text-sm text-gray-700">
                        <li> La gestion de vos identifiant et addresse e-mail, ainsi que la gestion de la sécurité (page 'Profil')</li>
                        <li> Quelques personnalisations de l'affichage des pages du site ('Préférences')</li>
                        <li> La possibilité de venir en aide à BDFI grâce à un formulaire générique présent sur toutes les pages du site</li>
                        <li> La possibilité d'utiliser BDFI pour gérer les collections SF-F-F de ta bibliothèque</li>
                    </ul>
                </div>
            </div>

            <div class="bg-gray-200 bg-opacity-25 grid grid-cols-1 md:grid-cols-2">
                <div class="p-6">
                    <div class="flex items-center">
                        <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" class="w-8 h-8 text-gray-400"><path d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <div class="ml-4 text-lg text-gray-600 leading-7 font-semibold"><a href="user/preferences">Préférences : personnalisations et fonctions</a></div>
                    </div>
                </div>

                <div class="p-6 border-t border-gray-200 md:border-l">
                    <div class="flex items-center">
                        <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" class="w-8 h-8 text-gray-400"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        <div class="ml-4 text-lg text-gray-600 leading-7 font-semibold"><a href="user/profile">Profil : données personnelles et sécurité</a></div>
                    </div>
                </div>

                @if (Auth::user()->gestion_biblio)
                    <div class="p-6 border-t border-gray-200 md:border-t-0 md:border-l">
                        <div class="flex items-center">
                            <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" class="w-8 h-8 text-gray-400"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            <div class="ml-4 text-lg text-gray-600 leading-7 font-semibold"><a href="/user/bibliotheque">Gestion de ma bibliothèque</a></div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

</x-app-layout>
