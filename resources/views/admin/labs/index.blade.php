<x-app-layout>
    <!-- Zone Labs : essais divers -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="/admin">Administration BDFI</a> &rarr;
            <a href="/admin/labs">Labs - essais divers</a>
        </h2>
    </x-slot>

    <div class="pt-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

                <div class="p-6">
                    <div class="p-2 text-2xl border-b border-yellow-800">
                        Essais en cours
                    </div>
                    <div class="p-2">
                        Aucun essai pour l'instant. Les outils stabilisés sont dans la zone
                        <x-admin.link lien='outils'>Outils</x-admin.link>.
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
