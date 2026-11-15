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
                            Création rapide
                        </div>
                        <div class="p-2">
                            Pour une variante d'oeuvre, attendez les nouveaux formulaires, passez par les tables... ou adressez vous au chef :-)
                        </div>
                        <div class="p-2">
                            <ul class="list-disc pl-4">
                                <li> <span class="font-semibold text-red-800 bg-yellow-200">(Revu)</span> <x-admin.link lien='formulaires/ajout-auteur'>Ajout auteur non déjà en base</x-admin.link></li>
                                <li> <span class="font-semibold text-red-800 bg-yellow-200">(Revus)(à compléter)</span> <x-admin.link lien='formulaires/ajout-publication'>Ajout publication parue</x-admin.link>
                                    ou <x-admin.link lien='formulaires/ajout-publication?statut=annonce'>à paraître</x-admin.link>, non déjà recensée</li>
                                <li> <span class="font-semibold text-red-800 bg-yellow-200">(Revu)</span> <x-admin.link lien='formulaires/ajout-editeur'>Ajout éditeur nouveau ou non déjà référencé</x-admin.link></li>
                                <li> <span class="font-semibold text-red-800 bg-yellow-200">(Revu)</span> <x-admin.link lien='formulaires/ajout-collection'>Ajout collection nouvelle ou non déjà référencée</x-admin.link></li>
                                <li> <span class="font-semibold text-red-800 bg-yellow-200">(A supprimer ?)</span> <x-admin.link lien='formulaires/ajout-texte'>Ajout d'une oeuvre non recensée</x-admin.link></li>
                                <li> <span class="font-semibold text-red-800 bg-yellow-200">(A faire)</span> <x-admin.link lien='formulaires/ajout-annee-prix'>Ajouter des gagnant d'une année d'un prix</x-admin.link>; ou passer d'abord par les <x-admin.link lien='rapports'>rapports</x-admin.link> pour visualiser ce qui manque</li>
                            </ul>
                        </div>
                    @endif

                    @if (auth()->user()->hasGuestRole())
                        <div class="p-2 text-2xl border-b border-yellow-800">
                            Corrections et améliorations
                        </div>
                        <div class="p-2">
                            <ul class="list-disc pl-4">
                                <li> <span class="font-semibold text-red-800 bg-yellow-200">(A revoir)</span> <x-admin.link lien='formulaires/modifier-auteur'>Modification auteur existant</x-admin.link></li>
                                <li> <span class="font-semibold text-red-800 bg-yellow-200">(A faire)</span> ajout-variant : Choix de deux titres (auteur, trad) non déjà reliés, et du type de lien. Valider pour ajouter un lien.<br /></li>
                                <li> <span class="font-semibold text-red-800 bg-yellow-200">(A faire)</span> inverser-variant : Recherche des variants via un titre - Afficher la liste, sélectionner, appliquer l'inversion</li>
                                <li> <span class="font-semibold text-red-800 bg-yellow-200">(A faire)</span> scinder-variant : Supprimer une liaison erronée - Recherche des variants via un titre - Afficher la liste, sélectionner, supprimer le lien</li>
                        </div>
                    @endif

                    @if (auth()->user()->hasGuestRole())
                        <div class="p-2 text-2xl border-b border-yellow-800">
                            Propositions
                        </div>
                        <div class="p-2">
                            Encore en réflexion !
                        </div>
                        <div class="p-2">
                            <ul class="list-disc pl-4">
                                <li><x-admin.link lien='formulaires/proposer-publication'>Proposition ajout ouvrage <span class="italic font-semibold">paru</span> non recensé</x-admin.link></li>
                            </ul>
                        </div>
                    @endif

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

                    @if (auth()->user()->hasMemberRole())
                        <div class="p-2 text-2xl border-b border-yellow-800">
                            Raccourcis sur formulaires table
                        </div>
                        <div class="p-2">
                            <ul class="list-disc pl-4">
                                <li><x-admin.link-ext lien='https://bdfi-v2.test/filament/events/create'>Ajout évènement non déjà recensé</x-admin.link-ext></li>
                                <li><x-admin.link-ext lien='https://bdfi-v2.test/filament/announcements/create'>Ajout remerciement ou annonce</x-admin.link-ext></li>
                            </ul>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

