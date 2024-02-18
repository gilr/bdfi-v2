<x-app-layout>
    <!-- Page d'accueil administration (jetstream) -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {!! __('Administration BDFI &rarr; Téléchargements') !!}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

                <div class="p-6">
                    <div class="border border-yellow-800 bg-yellow-100 rounded-lg p-2">
                        <p>Les liens suivants permettent de récupérer et télécharger le backup complet d'une table de la base. Les fichiers sont tous au format CSV. Attention, certaines tables "fournies" peuvent être un peu plus lente que les autres. Les liens inter-tables ne sont aujourd'hui représentés que par les ID dans les tables liées. Pas forcément pratique, on essaiera d'améliorer. Peut être utilisé sans riques... Bien au contraire, faire des sauvegardes régulières peut nous être utile un jour.</p>
                    </div>

                    <div class="pt-4">
                        <div class="p-2 text-2xl border-b border-yellow-800">
                            Tables autour des auteurs
                        </div>
                        <div class="p-2">
                            <ul class="list-disc pl-6">
                                <li><x-bdfi-adm-link lien='/admin/telechargements/Author'>auteurs</x-bdfi-adm-link></li>
                                <li><x-bdfi-adm-link lien='/admin/telechargements/Signature'>signatures</x-bdfi-adm-link></li>
                                <li><x-bdfi-adm-link lien='/admin/telechargements/Website'>sites web</x-bdfi-adm-link></li>
                                <li><x-bdfi-adm-link lien='/admin/telechargements/Relationship'>relations entre auteurs</x-bdfi-adm-link></li>
                                <li><x-bdfi-adm-link lien='/admin/telechargements/Country'>pays</x-bdfi-adm-link></li>
                                <li><x-bdfi-adm-link lien='/admin/telechargements/Translator'>traducteurs</x-bdfi-adm-link></li>
                                <li><x-bdfi-adm-link lien='/admin/telechargements/Illustrator'>illustrateurs</x-bdfi-adm-link></li>
                            </ul>
                        </div>
                    </div>
                    <div class="pt-4">
                        <div class="p-2 text-2xl border-b border-yellow-800">
                            Tables autour des publications
                        </div>
                        <div class="p-2">
                            <ul class="list-disc pl-6">
                                <li><x-bdfi-adm-link lien='/admin/telechargements/Publication'>Publications</x-bdfi-adm-link></li>
                                <li><x-bdfi-adm-link lien='/admin/telechargements/Collection'>Collections</x-bdfi-adm-link></li>
                                <li><x-bdfi-adm-link lien='/admin/telechargements/Publisher'>Editeurs</x-bdfi-adm-link></li>
                                <li><x-bdfi-adm-link lien='/admin/telechargements/Reprint'>Retirages</x-bdfi-adm-link></li>
                                <li><x-bdfi-adm-link lien='/admin/telechargements/TableOfContent'>Sommaires</x-bdfi-adm-link></li>
                            </ul>
                        </div>
                    </div>
                    <div class="pt-4">
                        <div class="p-2 text-2xl border-b border-yellow-800">
                            Tables autour des oeuvres
                        </div>
                        <div class="p-2">
                            <ul class="list-disc pl-6">
                                <li><x-bdfi-adm-link lien='/admin/telechargements/Title'>Oeuvres</x-bdfi-adm-link></li>
                                <li><x-bdfi-adm-link lien='/admin/telechargements/Cycle'>Cycles</x-bdfi-adm-link></li>
                            </ul>
                        </div>
                    </div>
                    <div class="pt-4">
                        <div class="p-2 text-2xl border-b border-yellow-800">
                            Tables des prix
                        </div>
                        <div class="p-2">
                            <ul class="list-disc pl-6">
                                <li><x-bdfi-adm-link lien='/admin/telechargements/Award'>Prix</x-bdfi-adm-link></li>
                                <li><x-bdfi-adm-link lien='/admin/telechargements/AwardCategory'>Catégories</x-bdfi-adm-link></li>
                                <li><x-bdfi-adm-link lien='/admin/telechargements/AwardWinner'>Lauréats</x-bdfi-adm-link></li>
                            </ul>
                        </div>
                    </div>
                    <div class="pt-4">
                        <div class="p-2 text-2xl border-b border-yellow-800">
                            Evènements et annonces
                        </div>
                        <div class="p-2">
                            <ul class="list-disc pl-6">
                                <li><x-bdfi-adm-link lien='/admin/telechargements/Event'>salons et évènements</x-bdfi-adm-link></li>
                                <li><x-bdfi-adm-link lien='/admin/telechargements/Announcement'>news et annonces sites</x-bdfi-adm-link></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
