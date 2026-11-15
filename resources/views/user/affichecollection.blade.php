<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Listing collection') }}
    </h2>
</x-slot>

<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
@php
$user = Auth::user();
// Chargement groupé (éditeurs, ouvrages, auteurs) : évite une requête par collection, ouvrage et auteur
$user->loadMissing([
    'collections_en_cours.publisher', 'collections_en_cours.publications.authors',
    'collections_quasi_ok.publisher', 'collections_quasi_ok.publications.authors',
    'collections_terminees.publisher', 'collections_terminees.publications.authors',
    'collections_en_pause.publisher', 'collections_en_pause.publications.authors',
]);
@endphp

    <div class="border-b-2 border-blue-400">
        <a class="font-semibold bg-blue-200 border px-2 py-1 m-2 border-blue-600 hover:bg-purple-100 hover:text-purple-800 hover:border-blue-800 rounded-t shadow-lg" href="/user/bibliotheque">En bref</a>
        <a class="font-semibold bg-blue-200 border px-2 py-1 m-2 border-blue-600 hover:bg-purple-100 hover:text-purple-800 hover:border-blue-800 rounded-t shadow-lg" href="/user/gestion-biblio">Gestion</a>
        <span class="font-semibold bg-gray-100 border px-2 py-1 m-2 border-gray-600 rounded-t shadow-lg">Listing</span>
        <a class="font-semibold bg-blue-200 border px-2 py-1 m-2 border-blue-600 hover:bg-purple-100 hover:text-purple-800 hover:border-blue-800 rounded-t shadow-lg" href="/user/mancoliste">Mancoliste</a>
    </div>

    @if (count($user->collections) == 0)
        @include('user._biblioempty', ['emptyMessage' => "Vous ne suivez aucune collection pour l'instant."])
    @elseif ((count($user->collections_en_cours) + count($user->collections_quasi_ok) + count($user->collections_terminees) + count($user->collections_en_pause)) == 0)
        @include('user._biblioempty', ['emptyMessage' => "Vos collections suivies sont toutes cachées : il n'y a rien à afficher."])
    @else

    <div class="pt-2 text-sm text-gray-700">
        <span class="font-semibold">Mode d'emploi :</span> <span class="font-mono text-xs bg-green-200 font-semibold border border-green-800 rounded px-1">+</span> ajoute un ouvrage à votre bibliothèque, <span class="font-mono text-xs bg-red-200 font-semibold border border-red-800 rounded px-1">-</span> le retire. Un clic sur le livre <x-front.display-icon-pub-owned /> d'un ouvrage possédé le marque abîmé <x-front.display-icon-pub-damaged />, un nouveau clic le remet en bon état.<br />
        <span class="font-semibold">Attention,</span> les modifications sont immédiates, sans demande de confirmation.
    </div>

    <script>
        // Appel POST sans rechargement ; renvoie la réponse JSON, ou null en cas d'échec
        function biblioPost(url, pub, extra = {}) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(Object.assign({ pub: pub }, extra)),
            })
            .then(r => r.ok ? r.json() : Promise.reject(r.status))
            .catch(() => { alert("La modification n'a pas pu être enregistrée."); return null; });
        }

        // Ligne d'ouvrage du Listing (Alpine) : ajout, retrait, bascule possédé <-> abîmé.
        // Affichage immédiat (sans attendre le serveur) ; l'enregistrement suit en arrière-plan, et en cas d'échec
        // l'état précédent est rétabli. Les appels d'une même ligne sont enchaînés pour rester dans l'ordre des clics.
        // Le nouvel état est diffusé (biblio-pub) pour synchroniser toutes les lignes du même ouvrage
        // (un ouvrage peut figurer dans plusieurs collections), et chaque ligne met à jour le décompte de son bloc.
        function biblioRow(pub, owned, damaged) {
            return {
                pub: pub, owned: owned, damaged: damaged, queue: Promise.resolve(),
                apply(owned, damaged) {
                    window.dispatchEvent(new CustomEvent('biblio-pub', { detail: { pub: this.pub, owned: owned, damaged: damaged } }));
                },
                call(url, owned, damaged, extra = {}) {
                    const before = { owned: this.owned, damaged: this.damaged };
                    this.apply(owned, damaged);
                    this.queue = this.queue.then(() => biblioPost(url, this.pub, extra).then(data => {
                        if (!data) this.apply(before.owned, before.damaged);
                    }));
                },
                add()           { this.call('/user/ajouter-publication', true, false); },
                remove()        { this.call('/user/retirer-publication', false, false); },
                toggleDamaged() { this.call('/user/etat-publication', true, !this.damaged, { status: this.damaged ? 'possede' : 'abime' }); },
                sync(d) {
                    if (d.pub !== this.pub) return;
                    if (d.owned !== this.owned) this.$dispatch('biblio-count', { delta: d.owned ? 1 : -1 });
                    this.owned = d.owned;
                    this.damaged = d.damaged;
                },
            };
        }
    </script>

    <div>
        @if ((count($user->collections_en_cours) + count($user->collections_quasi_ok)) != 0)
            <div class='pt-2 font-semibold'>
                Collections suivies et en cours :
            </div>
            <div class=' columns-1 md:columns-2 xl:columns-3 text-base gap-2 self-center sm:w-full'>
                @foreach ($user->collections_en_cours->concat($user->collections_quasi_ok) as $collection)
                    @include('user._bibliocollectionlisting')
                @endforeach
            </div>
        @endif

        @if ((count($user->collections_terminees) + count($user->collections_en_pause)) != 0)
            <div class='pt-2 font-semibold'>
                Collections terminées ou en pause :
            </div>
            <div class=' columns-1 md:columns-2 xl:columns-3 text-base gap-2 self-center sm:w-full'>
                @foreach ($user->collections_terminees->concat($user->collections_en_pause) as $collection)
                    @include('user._bibliocollectionlisting')
                @endforeach
            </div>
        @endif
    </div>
    @endif

            </div>
        </div>
    </div>
</div>

</x-app-layout>
