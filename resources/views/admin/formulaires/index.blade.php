<x-app-layout>
    <!-- Page d'accueil administration (jetstream) -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="/admin">Administration BDFI</a> &rarr;
            <a href="/admin/formulaires">Formulaires</a>
        </h2>
    </x-slot>

    <x-admin.discovery-notice :allowed="auth()->user()->hasProponentRole()" />

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

                <div class="p-6">
                        <div class="p-2 text-2xl border-b border-yellow-800">
                            Vérifications rapides
                        </div>
                        <div class="p-2">
                            Ouvre une fenêtre pop-up pour une recherche rapide, permettant de vérifier l'existence et de donner accès (si vous avez les droits) aux fiches 'gestion tables" des enregistrements trouvés.
                        </div>
                        <div class="p-2">
                            <livewire:author-search intro="" label="Existence d'un auteur" />
                            <livewire:publisher-search intro="" label="Existence d'un éditeur" />
                            <livewire:collection-search intro="" label="Existence d'une collection" />
                            <livewire:publication-search intro="" label="Existence d'un ouvrage" />
                            <livewire:title-search intro="" label="Existence d'un texte" />
                        </div>

                    @if (auth()->user()->hasGuestRole())
                        <div class="p-2 text-2xl border-b border-yellow-800">
                            Créations rapides, liées aux ouvrages
                        </div>
                        <div class="p-2">
                            Pour les variantes d'oeuvre, voir plus bas
                        </div>
                        <div class="p-2">
                            <ul class="list-disc pl-4">
                                <li><x-admin.link lien='formulaires/ajout-auteur'>Ajout auteur non déjà en base</x-admin.link></li>
                                <li> <span class="font-semibold text-red-800 bg-yellow-200">(Encore à compléter)</span> <x-admin.link lien='formulaires/ajout-publication'>Ajout publication parue</x-admin.link>
                                    ou <x-admin.link lien='formulaires/ajout-publication?statut=annonce'>à paraître</x-admin.link>, non déjà recensée</li>
                                <li><x-admin.link lien='formulaires/ajout-editeur'>Ajout éditeur nouveau ou non déjà référencé</x-admin.link></li>
                                <li><x-admin.link lien='formulaires/ajout-collection'>Ajout collection nouvelle ou non déjà référencée</x-admin.link></li>
                                <li> <span class="font-semibold text-red-800 bg-yellow-200">(A supprimer à terme ?)</span> <x-admin.link lien='formulaires/ajout-texte'>Ajout d'une oeuvre non recensée</x-admin.link></li>
                            </ul>
                        </div>
                    @endif

                    @if (auth()->user()->hasGuestRole())
                        <div class="p-2 text-2xl border-b border-yellow-800">
                            Corrections et améliorations
                        </div>
                        <div class="p-2">
                            <ul class="list-disc pl-4">
                                <li><x-admin.link lien='formulaires/modifier-auteur'>Modification auteur existant</x-admin.link></li>
                                <li><x-admin.link lien='formulaires/gerer-variantes'>Variantes de textes</x-admin.link> : après choix d'un texte, affiche son ensemble (parent et variantes) pour ajouter ou retirer une variante, changer un type, ou désigner un autre parent</li>
                            </ul>
                        </div>
                    @endif

                    {{-- Seule section visible pour un contributeur (avec les vérifications rapides) --}}
                        <div class="p-2 text-2xl border-b border-yellow-800">
                            Propositions
                        </div>
                        <div class="p-2">
                            Va être totalement remis à plat : les propositions seront stockées au format JSON dans une table spécifique, et non dans la table 'normale' des publications. Cela permettra plus facilement de gérer des données encore manquantes (auteur ou cycle non existant en base), et est plus ouvert pour évoluer vers d'autres propositions (d'autres données, ou en modification en plus de création).
                        </div>
                        <div class="p-2">
                            <ul class="list-disc pl-4">
                                <li><x-admin.link lien='formulaires/proposer-publication'>Proposition ajout ouvrage <span class="italic font-semibold">paru</span> non recensé</x-admin.link></li>
                            </ul>
                        </div>

                    @if (auth()->user()->hasGuestRole())
                        <div class="p-2 text-2xl border-b border-yellow-800">
                            Autres opérations
                        </div>
                        <div class="p-2">
                            Dans la prochaine version, il sera possible de modifier la date de parution, ou de valider une proposition localement. Pour l'instant, ces pages listent les ouvrages concernés, en indiquant le lien pour d'accès à leur fiche côté gestion des tables.
                        </div>
                        <div class="p-2">
                            <ul class="list-disc pl-4">
                                <li><x-admin.link lien='formulaires/publications-proposees'>Validation de proposition</x-admin.link></li>
                                <li><x-admin.link lien='formulaires/programmes-echus'>Confirmation publication programmée, date échue</x-admin.link></li>
                                <li><x-admin.link lien='formulaires/programmes-non-echus'>Confirmation publication programmée, date non échue</ x-admin.link></li>
                            </ul>
                        </div>
                    @endif

                    @if (auth()->user()->hasGuestRole())
                        <div class="p-2 text-2xl border-b border-yellow-800">
                            Autres ajouts ou modifications
                        </div>
                        <div class="p-2">
                            <ul class="list-disc pl-4">
                                <li><x-admin.link lien='formulaires/ajout-annee-prix'>Ajout des gagnants d'une année d'un prix</x-admin.link>; ou passer d'abord par les <x-admin.link lien='rapports'>rapports</x-admin.link> pour visualiser ce qui manque</li>
                                <li><x-admin.link lien='formulaires/ajout-evenement'>Ajout d'un salon ou festival, ou de son occurence annuelle</x-admin.link>; ou voir d'abord les <x-admin.link lien='rapports'>rapports</x-admin.link> pour des propositions</li>
                                <li><x-admin.link lien='formulaires/ajout-annonce'>Ajout d'une annonce ou d'un remerciement</x-admin.link></li>
                            </ul>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

