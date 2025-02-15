<x-app-layout>
    <!-- Page d'accueil administration (jetstream) -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {!! __('Administration BDFI &rarr; Formulaires d\'ajout rapide') !!}
        </h2>
    </x-slot>

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

                    @if (auth()->user()->hasProponentRole())
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

                    @if (auth()->user()->hasMemberRole())
                        <div class="p-2 text-2xl border-b border-yellow-800">
                            Création rapide
                        </div>
                        <div class="p-2">
                            Attention, toute nouvelle publication devra être attachée à une oeuvre au moins (un contenu). S'il s'agit d'une première publication de l'oeuvre, il est plus simple de créer d'abord cette oeuvre ("Ajout d'une oeuvre") puis de créer la publication. Les formulaires seront améliorés pour pouvoir également renseigner d'autres informations (les auteurs par exemple) en une seule étape. Une oeuvre peut être un roman, un recueil, une nouvelle, un essai... Pour une variante d'oeuvre, passez par les tables (ou adressez vous au chef :-))
                        </div>
                        <div class="p-2">
                            <ul class="list-disc pl-4">
                                <li><x-admin.link lien='formulaires/ajout-auteur'>Ajout auteur non déjà en base</x-admin.link>
                                --- ou --- <x-admin.link lien='formulaires/modifier-auteur'>Modification auteur existant</x-admin.link></li>
                                <li><x-admin.link lien='formulaires/ajout-texte'>Ajout d'une oeuvre non recensée</x-admin.link></li>
                                <li><x-admin.link lien='formulaires/ajout-publication'>Ajout publication parue non déjà recensée</x-admin.link></li>
                                <li><x-admin.link lien='formulaires/programme-parution'>Ajout publication à paraître</x-admin.link></li>
                                <li><x-admin.link lien='formulaires/ajout-editeur'>Ajout éditeur nouveau ou inconnu</x-admin.link></li>
                                <li><x-admin.link lien='formulaires/ajout-collection'>Ajout collection nouvelle ou inconnue</x-admin.link></li>
                            </ul>
                        </div>
                        <div class="p-2 text-2xl border-b border-yellow-800">
                            Autres opérations
                        </div>
                        <div class="p-2">
                            <ul class="list-disc pl-4">
                                <li><x-admin.link lien='formulaires/publications-proposees'>Validation de proposition</x-admin.link></li>
                                <li><x-admin.link lien='formulaires/programmes-echus'>Confirmation publication programmée, date échue</x-admin.link></li>
                                <li><x-admin.link lien='formulaires/programmes-non-echus'>Confirmation publication programmée, date non échue</ x-admin.link></li>
                            </ul>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
