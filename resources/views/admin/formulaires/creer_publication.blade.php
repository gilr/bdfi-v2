<x-app-layout>
    <!-- Création rapide d'une publication parue ou à paraître -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="/admin">Administration BDFI</a> &rarr;
            <a href="/admin/formulaires">Formulaires</a> &rarr;
            Ajout rapide publication (parue ou à paraître)
        </h2>
    </x-slot>

    <x-admin.discovery-notice :allowed="auth()->user()->hasMemberRole()" />

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="border border-blue-800 rounded-md p-1 m-1">
                Ajout d'une publication parue ou à paraître, non déjà recensée.
                <livewire:publication-search intro="Si besoin, (re-)vérifier l'absence" label="Recherche ouvrage" />
            </div>

            @if ($errors->any())
                <div class="p-2">
                    <div class="p-2 text-xl border-b border-red-800 bg-red-100">
                        Erreur lors de la tentative d'ajout
                    </div>
                    <div class="p-2">
                        <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if (!empty($status))
                <div class="p-2">
                    <div class="p-2 text-xl border-b border-green-800 bg-green-100">
                        Publication créée : {{ $label }} (#{{ $id }})
                    </div>
                    <div class="p-2">
                        Page de l'ouvrage : <x-front.lien-standard link="/ouvrages/{{ $slug }}">{{ $label }}</x-front.lien-standard>
                        — fiche à compléter : <a class="text-red-700" href="/filament/{{ $filament }}/{{ $id }}" target="_blank">fiche {{ $area }} <span class="font-bold">{{ $id }}</span></a>
                        (nouvel onglet : auteurs, éditeur et collection, informations du livre si possédé…).
                    </div>
                    @if (!empty($content))
                        <div class="p-2">
                            Contenu principal : texte {{ $content['created'] ? 'créé avec la publication' : 'existant rattaché' }}
                            (<a class="text-red-700" href="/textes/{{ $content['slug'] }}" target="_blank">page du texte #{{ $content['id'] }}</a>).
                        </div>
                    @endif
                    <div class="p-2">
                        {{-- Étape suivante : le reste du sommaire. Formulaire sommaire à venir ; en attendant, la fiche Filament --}}
                        <a href="/filament/{{ $filament }}/{{ $id }}" target="_blank"
                           class="inline-block bg-gray-300 px-4 py-2 border border-blue-800 rounded">
                            Faut-il renseigner un sommaire ou contenu ? →
                        </a>
                        <span class="text-xs text-gray-500">(formulaire sommaire à venir ; en attendant, onglet contenu de la fiche Filament)</span>
                    </div>
                    <div class="p-2 bg-green-100">
                        Vous pouvez en créer une autre :
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="/admin/formulaires/ajout-publication" class="bg-white shadow-xl rounded p-4 m-1"
                  x-data="{ st: @js($pubStatus) }">
                @csrf

                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <span class="w-32 font-semibold">Parution *</span>
                    <label><input type="radio" name="publication_status" x-model="st" value="{{ App\Enums\PublicationStatus::PUBLIE->value }}"> parue</label>
                    <label class="ml-2"><input type="radio" name="publication_status" x-model="st" value="{{ App\Enums\PublicationStatus::ANNONCE->value }}"> à paraître (programme)</label>
                </div>

                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <label for="name" class="w-32 font-semibold">Titre *</label>
                    <input id="name" name="name" value="{{ old('name') }}" maxlength="128" required class="w-full border rounded bg-yellow-100 max-w-xl"
                           placeholder="Titre de l'ouvrage, tel qu'imprimé en début d'ouvrage (page de titre)">
                </div>

                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <label for="type" class="w-32 font-semibold">Type *</label>
                    <select id="type" name="type" required class="w-64 border rounded bg-yellow-100">
                        <option value="">— choisir —</option>
                        @foreach (App\Enums\PublicationContent::cases() as $c)
                            <option value="{{ $c->value }}" @selected(old('type') === $c->value)>{{ $c->getLabel() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <label for="support" class="w-32 font-semibold">Support *</label>
                    <select id="support" name="support" class="w-48 border rounded bg-yellow-100">
                        @foreach (App\Enums\PublicationSupport::cases() as $c)
                            <option value="{{ $c->value }}" @selected(old('support', App\Enums\PublicationSupport::PAPIER->value) === $c->value)>{{ $c->getLabel() }}</option>
                        @endforeach
                    </select>
                    <label for="format" class="font-semibold ml-2">Format</label>
                    <select id="format" name="format" class="w-48 border rounded bg-yellow-100">
                        @foreach (App\Enums\PublicationFormat::cases() as $c)
                            <option value="{{ $c->value }}" @selected(old('format', App\Enums\PublicationFormat::INCONNU->value) === $c->value)>{{ $c->getLabel() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <label for="approximate_parution" class="w-32 font-semibold">
                        <span x-show="st !== '{{ App\Enums\PublicationStatus::ANNONCE->value }}'">Parution *</span>
                        <span x-show="st === '{{ App\Enums\PublicationStatus::ANNONCE->value }}'" x-cloak>Parution prévue *</span>
                    </label>
                    <input id="approximate_parution" name="approximate_parution" value="{{ old('approximate_parution') }}" maxlength="10" required
                           class="w-32 border rounded bg-yellow-100 font-mono" placeholder="AAAA-MM-00">
                    <span class="text-xs text-gray-500">AAAA-MM-00 (1983-05-00 pour mai 1983), AAAA-00-00 si l'année seule est connue, AAAA-MM-JJ si le jour est donné, AAAA-Tn-00 (trimestre)
                        <span x-show="st !== '{{ App\Enums\PublicationStatus::ANNONCE->value }}'">, AAAA-circa si approximative</span></span>
                </div>

                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <label for="is_genre" class="w-32 font-semibold">Genres réf. *</label>
                    <select id="is_genre" name="is_genre" class="w-48 border rounded bg-yellow-100">
                        @foreach (App\Enums\GenreAppartenance::cases() as $c)
                            <option value="{{ $c->value }}" @selected(old('is_genre') === $c->value)>{{ $c->getLabel() }}</option>
                        @endforeach
                    </select>
                    <label for="genre_stat" class="font-semibold ml-2">Genre *</label>
                    <select id="genre_stat" name="genre_stat" required class="w-48 border rounded bg-yellow-100">
                        <option value="">— choisir —</option>
                        @foreach (App\Enums\GenreStat::cases() as $c)
                            <option value="{{ $c->value }}" @selected(old('genre_stat') === $c->value)>{{ $c->getLabel() }}</option>
                        @endforeach
                    </select>
                    <label for="target_audience" class="font-semibold ml-2">Public *</label>
                    <select id="target_audience" name="target_audience" class="w-48 border rounded bg-yellow-100">
                        @foreach (App\Enums\AudienceTarget::cases() as $c)
                            <option value="{{ $c->value }}" @selected(old('target_audience') === $c->value)>{{ $c->getLabel() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <label for="isbn" class="w-32 font-semibold">ISBN</label>
                    <input id="isbn" name="isbn" value="{{ old('isbn') }}" maxlength="18" class="w-48 border rounded bg-yellow-100 font-mono"
                           placeholder="978-2-...">
                    <label class="ml-2"><input type="checkbox" name="no_isbn" value="1" @checked(old('no_isbn'))> pas d'ISBN</label>
                    <span class="text-xs text-gray-500">« pas d'ISBN » : ouvrage ancien, fanzine… (affiché n/a) ; laissé vide : ISBN inconnu</span>
                </div>

                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <span class="w-32 font-semibold">Physique</span>
                    <label><input type="checkbox" name="is_hardcover" value="1" @checked(old('is_hardcover'))> relié</label>
                    <label class="ml-2"><input type="checkbox" name="has_dustjacket" value="1" @checked(old('has_dustjacket'))> jaquette</label>
                    <label class="ml-2"><input type="checkbox" name="has_coverflaps" value="1" @checked(old('has_coverflaps'))> couverture à rabats</label>
                </div>

                <input type="hidden" name="private" value="">

                <div class="mb-1">
                    <livewire:entity-picker
                        model="App\Models\Publisher"
                        field-name="publisher"
                        label="Éditeur"
                        placeholder="Rechercher un éditeur existant par nom…"
                        noun="un nouvel éditeur"
                        :old-id="old('publisher_id')"
                        :old-name="old('publisher_name')"
                    />
                </div>

                <div class="flex flex-wrap items-start gap-3 mb-1">
                    <div class="w-64">
                        <livewire:entity-picker
                            model="App\Models\Collection"
                            field-name="collection"
                            label="Collection"
                            placeholder="Rechercher une collection existante par nom…"
                            :select-only="true"
                            :old-id="old('collection_id')"
                        />
                    </div>
                    <div>
                        <label for="collection_number" class="text-sm block mb-1">N° dans la collection</label>
                        <input id="collection_number" name="collection_number" value="{{ old('collection_number') }}" maxlength="16"
                               class="w-32 border rounded bg-yellow-100" placeholder="Si numérotée">
                    </div>
                </div>
                <div class="text-xs text-gray-500 mb-1">Collection non trouvée ? Créez-la d'abord via <x-admin.link lien='/admin/formulaires/ajout-collection'>ce formulaire</x-admin.link>, puis revenez ici.</div>

                <div class="flex flex-wrap items-start gap-3 mb-1">
                    <div class="w-64">
                        <livewire:entity-picker
                            model="App\Models\Author"
                            field-name="author1"
                            label="Auteur 1"
                            placeholder="Rechercher un auteur…"
                            :select-only="true"
                            :old-id="old('author1_id')"
                        />
                    </div>
                    <div class="w-64">
                        <livewire:entity-picker
                            model="App\Models\Author"
                            field-name="author2"
                            label="Auteur 2"
                            placeholder="Rechercher un auteur…"
                            :select-only="true"
                            :old-id="old('author2_id')"
                        />
                    </div>
                </div>
                <div class="text-xs text-gray-500 mb-1">Auteur non trouvé, ou plus de deux auteurs ? Laissez vide ici et complétez-le ensuite dans Filament.</div>

                <livewire:main-content-picker
                    :old-choice="old('title_choice')"
                    :old-name="old('name')"
                    :old-author1="old('author1_id')"
                    :old-author2="old('author2_id')"
                    :old-type="old('new_title_type')"
                    :old-copyright="old('new_title_copyright')"
                    :old-title-vo="old('new_title_vo')"
                    :old-translators="old('new_title_translators')"
                    :old-publication-type="old('type')"
                    :old-extract="old('new_title_extract')"
                    :old-novelization="old('new_title_novelization')"
                />

                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <button type="submit" @disabled(! auth()->user()->hasMemberRole())  class="bg-blue-400 font-semibold border border-blue-800 rounded px-4 py-2 disabled:opacity-50 disabled:cursor-not-allowed">Créer la publication</button>
                    <span class="text-xs text-gray-500">* obligatoire. Le sommaire (contenu) se renseigne après la création.</span>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
