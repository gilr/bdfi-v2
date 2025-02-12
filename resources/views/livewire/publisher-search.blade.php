<div class="inline-block">
    <!-- Bouton pour ouvrir la modale -->
    {{ $intro }}
    <button class="bg-blue-200 font-semibold border border-blue-800 rounded px-2 py-1 m-1" type="button" wire:click="$set('showModal', true)">{{ $label }}</button>

    <!-- Modale -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex justify-center items-center shadow-lg shadow-indigo-500/50">
            <div class="bg-white p-6 rounded-lg shadow-lg w-9/12">
                <h2 class="text-lg font-semibold mb-4">Rechercher un éditeur</h2>

                <!-- Champ de saisie de la recherche -->
                <input type="text" wire:model.live.debounce.500ms="search" class="border p-2 w-full mb-4" placeholder="Entrez un extrait de nom...">

                <!-- Présentation des résultats -->

                @if($publishers && $publishers->isNotEmpty())
                    <!-- Liens de pagination -->
                    <div class="mt-4">
                        Cliquer sur un éditeur ouvre sa page dans un nouvel onglet.
                    </div>
                    <div class="mt-4">
                        {{ $publishers->links() }}
                    </div>

                    <!-- Liste des résultats -->
                    <div class='grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-0 lg:gap-0.5 border py-2"'>
                        @foreach($publishers as $publisher)
                            <div>
                                <x-admin.link-ext lien='/editeurs/{{ $publisher->slug }}'>
                                    {{ $publisher->name }}
                                </x-admin.link-ext>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p>Aucun éditeur trouvé.</p>
                @endif

                <!-- Bouton pour fermer la modale -->
                <button type="button" wire:click="$set('showModal', false)" class="mt-4 bg-gray-300 px-4 py-2 rounded">
                    Fermer
                </button>
            </div>
        </div>
    @endif
</div>
