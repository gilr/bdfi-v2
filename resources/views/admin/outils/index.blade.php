<x-app-layout>
    <!-- Page d'accueil administration (jetstream) -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {!! __('Administration BDFI &rarr; Outils') !!}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

                <div class="p-6">
                    <div class="p-2 text-2xl border-b border-yellow-800">
                        Anniversaire pour Facebook
                    </div>
                    <div class="p-2">
                        <ul class="list-disc pl-4">
                            <li><x-admin.link lien='outils/anniversaires-fb-jour'>Anniversaires du jour</x-admin.link></li>
                            <li><x-admin.link lien='outils/anniversaires-fb-semaine'>Anniversaires  de la semaine</x-admin.link></li>
                            <li><x-admin.link lien='outils/anniversaires-fb-mois'>Anniversaires du mois</x-admin.link></li>
                        </ul>
                    </div>
                    <div class="p-2 text-2xl border-b border-yellow-800">
                        Autre outils
                    </div>
                    <div class="p-2">
                        <ul class="list-disc pl-4">
                            <li> <!-- <a class="text-yellow-700 border-b border-yellow-500 hover:bg-yellow-100 hover:border-purple-800" href="{{ url('/admin/outils/conversion-sommaire') }}">Conversion au format maison</a> --> (Outils de conversion maison non porté)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


