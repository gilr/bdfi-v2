<x-app-layout>
    <!-- Page d'accueil administration (jetstream) -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="/admin">Administration BDFI</a> &rarr;
            <a href="/admin/outils">Labs - tests & outils</a>
        </h2>
    </x-slot>

    <div class="pt-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

                <div class="p-6">
                    <div class="p-2 text-2xl border-b border-yellow-800">
                        Transformation entre formats
                    </div>
                    <div class="p-2">
                        <ul class="list-disc pl-4">
                            <li><x-admin.link lien='labs/conversion-format'>Conversion de format</x-admin.link></li>
                        </ul>
                    </div>
                    <div class="p-2">
                        <ul class="list-disc pl-4">
                            <li><x-admin.link lien='labs/conversion-content'>Conversion sommaire</x-admin.link></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
