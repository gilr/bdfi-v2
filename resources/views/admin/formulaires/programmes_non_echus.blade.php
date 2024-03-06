<x-app-layout>
    <!-- Page d'accueil administration (jetstream) -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {!! __('Administration BDFI &rarr; Formulaires &rarr; Lister les programmes futurs') !!}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                A VENIR
                Ici, il est possible d'éventuellement valider la publication (elle est bien parue à la date indiquée), soit la valider en ajustant la date de publication indiquée, soit la postponer (repousser la date), soit la repousser sans date (0000-00-00), soit définitivement le tagger 'jamais paru'.
            </div>
        </div>
    </div>
</x-app-layout>


</div>