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
                                <li><x-admin.link lien='formulaires/ajout-auteur'>Ajouter un auteur non en base</x-admin.link></li>
                                <li> <span class="font-semibold text-red-800 bg-yellow-200">A compléter </span><x-admin.link lien='formulaires/ajout-publication'>Ajouter une publication parue</x-admin.link>
                                    ou <x-admin.link lien='formulaires/ajout-publication?statut=annonce'>à paraître</x-admin.link>, non déjà recensée</li>
                                <li><span class="font-semibold text-red-800 bg-yellow-200">A FAIRE </span>Ajouter, compléter ou corriger un sommaire</li>
                                <li><x-admin.link lien='formulaires/ajout-editeur'>Ajouter un éditeur non déjà référencé</x-admin.link></li>
                                <li><x-admin.link lien='formulaires/ajout-collection'>Ajouter une collection non déjà référencée</x-admin.link></li>
                                <li> <span class="font-semibold text-red-800 bg-yellow-200">(A supprimer à terme ?)</span> <x-admin.link lien='formulaires/ajout-texte'>Ajouter une oeuvre non recensée</x-admin.link></li>
                            </ul>
                        </div>
                    @endif

                    @if (auth()->user()->hasGuestRole())
                        <div class="p-2 text-2xl border-b border-yellow-800">
                            Modifications et corrections
                        </div>
                        <div class="p-2">
                            Concernant les programmations, il sera possible dans la prochaine version de modifier la date de parution localement. Pour l'instant, ces pages listent les ouvrages concernés, en indiquant le lien pour d'accès à leur fiche côté gestion des tables.
                        </div>
                        <div class="p-2">
                            <ul class="list-disc pl-4">
                                <li><x-admin.link lien='formulaires/modifier-auteur'>Modifier un auteur existant</x-admin.link></li>
                                <li><x-admin.link lien='formulaires/modifier-publication'>Modifier ou améliorer une publication</x-admin.link></li>
                                <li><x-admin.link lien='formulaires/verifier-publication'>Passer une publication à "vérifié" et compléter les infos "fines"</x-admin.link>(il faut l'avoir entre les mains)</li>
                                <li><x-admin.link lien='formulaires/gerer-variantes'>Gérer les variantes de textes</x-admin.link> (ajouter, retirer, changer le parent)</li>
                                <li><span class="font-semibold text-red-800 bg-yellow-200">A REVOIR </span><x-admin.link lien='formulaires/programmes-echus'>Lister, ajuster ou confirmer une publication programmée, date échue</x-admin.link></li>
                                <li><span class="font-semibold text-red-800 bg-yellow-200">A REVOIR </span><x-admin.link lien='formulaires/programmes-non-echus'>Lister et ajuster une publication programmée, date non échue</ x-admin.link></li>
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
                                <li><span class="font-semibold text-red-800 bg-yellow-200">A REVOIR </span><x-admin.link lien='formulaires/proposer-publication'>Proposer l'ajout d'un ouvrage paru non recensé</x-admin.link> (le sommaire peut être omis)</li>
                                <li><span class="font-semibold text-red-800 bg-yellow-200">A FAIRE </span><x-admin.link lien='formulaires/proposer-correction-publication'>Proposer une modification d'un ouvrage recensé</x-admin.link></li>
                                <li><span class="font-semibold text-red-800 bg-yellow-200">A FAIRE </span>Proposer un ajout ou complément de sommaire</li>
                                @if (auth()->user()->hasGuestRole())
                                    <li><span class="font-semibold text-red-800 bg-yellow-200">A FAIRE </span>Consulter les propositions</li>
                                    <li><span class="font-semibold text-red-800 bg-yellow-200">A REVOIR </span><x-admin.link lien='formulaires/publications-proposees'>Ajuster et/ou valider une proposition</x-admin.link></li>
                                @endif
                            </ul>
                        </div>

                    @if (auth()->user()->hasGuestRole())
                        <div class="p-2 text-2xl border-b border-yellow-800">
                            Autres ajouts ou modifications
                        </div>
                        <div class="p-2">
                            <ul class="list-disc pl-4">
                                <li><x-admin.link lien='formulaires/ajout-annee-prix'>Ajouter des gagnants d'une année d'un prix</x-admin.link>; ou passer d'abord par les <x-admin.link lien='rapports'>rapports</x-admin.link> pour visualiser ce qui manque</li>
                                <li><x-admin.link lien='formulaires/ajout-evenement'>Ajouter un salon ou festival, ou son occurence annuelle</x-admin.link>; ou voir d'abord les <x-admin.link lien='rapports'>rapports</x-admin.link> pour des propositions</li>
                                <li><x-admin.link lien='formulaires/ajout-annonce'>Ajouter une annonce ou un remerciement</x-admin.link></li>
                            </ul>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

