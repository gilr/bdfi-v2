<x-app-layout>
    <!-- Proposition d'ajout d'un ouvrage paru, par un contributeur -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="/admin">Administration BDFI</a> &rarr;
            <a href="/admin/formulaires">Formulaires</a> &rarr;
            Proposer l'ajout d'un ouvrage paru
        </h2>
    </x-slot>

    <x-admin.discovery-notice :allowed="auth()->user()->hasProponentRole()" />

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="border border-blue-800 rounded-md p-1 m-1">
                Proposition d'ajout d'un ouvrage <span class="font-semibold">déjà paru</span> et non recensé. Votre proposition sera étudiée par l'équipe BDFI avant d'être ajoutée à la base ; un ouvrage déjà référencé est refusé.
                <livewire:publication-search intro="Avant de proposer, vérifiez l'absence" label="Recherche ouvrage" />
            </div>

            @if ($errors->any())
                <div class="p-2">
                    <div class="p-2 text-xl border-b border-red-800 bg-red-100">
                        La proposition n'a pas été envoyée
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

            @if ($sent)
                <div class="p-2">
                    <div class="p-2 text-xl border-b border-green-800 bg-green-100">
                        Proposition envoyée : {{ $sent }}
                    </div>
                    <div class="p-2">
                        Merci ! Elle apparaît ci-dessous dans « Mes propositions ». Vous pouvez en proposer une autre.
                    </div>
                </div>
            @endif
            @if (session('withdrawn'))
                <div class="p-2 bg-gray-100 border border-gray-400">Proposition retirée : {{ session('withdrawn') }}</div>
            @endif
        </div>
    </div>

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="/admin/formulaires/proposer-publication" class="bg-white shadow-xl rounded p-4 m-1">
                @csrf

                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <label for="name" class="w-32 font-semibold">Titre *</label>
                    <input id="name" name="name" value="{{ old('name') }}" maxlength="128" required class="w-full border rounded bg-yellow-100 max-w-xl"
                           placeholder="Titre de l'ouvrage, tel qu'imprimé en couverture">
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
                    <label for="approximate_parution" class="w-32 font-semibold">Parution *</label>
                    <input id="approximate_parution" name="approximate_parution" value="{{ old('approximate_parution') }}" maxlength="10" required
                           class="w-32 border rounded bg-yellow-100 font-mono" placeholder="AAAA-MM-00">
                    <span class="text-xs text-gray-500">AAAA-MM-00 (1983-05-00 pour mai 1983), AAAA-00-00 si l'année seule est connue, AAAA-MM-JJ si le jour est donné, AAAA-Tn-00 (trimestre), AAAA-circa si approximative</span>
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
                    <span class="text-xs text-gray-500">« pas d'ISBN » : ouvrage ancien, fanzine… ; laissé vide : ISBN inconnu</span>
                </div>

                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="w-32 font-semibold">Physique</span>
                    <label><input type="checkbox" name="is_hardcover" value="1" @checked(old('is_hardcover'))> relié</label>
                    <label class="ml-2"><input type="checkbox" name="has_dustjacket" value="1" @checked(old('has_dustjacket'))> jaquette</label>
                    <label class="ml-2"><input type="checkbox" name="has_coverflaps" value="1" @checked(old('has_coverflaps'))> couverture à rabats</label>
                </div>

                <div class="mb-1">
                    <livewire:entity-picker
                        model="App\Models\Publisher"
                        field-name="publisher"
                        label="Éditeur *"
                        placeholder="Rechercher un éditeur existant par nom…"
                        noun="un éditeur non référencé"
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
                <div class="text-xs text-gray-500 mb-2">Collection absente de la liste ? Indiquez-la dans les notes ci-dessous.</div>

                @foreach ([1, 2] as $n)
                    <div class="flex flex-wrap items-start gap-3 mb-1">
                        <div class="w-64">
                            <livewire:entity-picker
                                model="App\Models\Author"
                                field-name="author{{ $n }}"
                                label="Auteur {{ $n }}"
                                placeholder="Rechercher un auteur…"
                                :select-only="true"
                                :old-id="old('author' . $n . '_id')"
                            />
                        </div>
                        <div>
                            <span class="text-sm block mb-1">Auteur non référencé</span>
                            <input name="author{{ $n }}_first_name" value="{{ old('author' . $n . '_first_name') }}" maxlength="32" placeholder="Prénom"
                                   class="w-36 border rounded bg-yellow-100" aria-label="Prénom de l'auteur {{ $n }}">
                            <input name="author{{ $n }}_name" value="{{ old('author' . $n . '_name') }}" maxlength="32" placeholder="Nom"
                                   class="w-40 border rounded bg-yellow-100" aria-label="Nom de l'auteur {{ $n }}">
                        </div>
                    </div>
                @endforeach
                <div class="text-xs text-gray-500 mb-2">Auteur non trouvé dans la recherche : saisissez son prénom et son nom. Plus de deux auteurs : indiquez les autres dans les notes.</div>

                <div class="mb-2">
                    <label for="private">Notes pour l'équipe (sources, liens, collection ou auteurs manquants...) :</label>
                    <textarea id="private" name="private" rows="3" maxlength="5000" class="border p-2 w-full bg-yellow-100">{{ old('private') }}</textarea>
                </div>

                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <button type="submit" @disabled(! auth()->user()->hasProponentRole()) class="bg-blue-400 font-semibold border border-blue-800 rounded px-4 py-2 disabled:opacity-50 disabled:cursor-not-allowed">Envoyer la proposition</button>
                    <span class="text-xs text-gray-500">* obligatoire. Le sommaire se proposera séparément, une fois l'ouvrage référencé.</span>
                </div>
            </form>

            <div class="bg-white shadow-xl rounded p-4 m-1 mt-4">
                <div class="text-xl border-b border-yellow-800 mb-2">Mes propositions</div>
                @if ($mine->isEmpty())
                    <p class="italic">Aucune proposition pour l'instant.</p>
                @else
                    <table class="table-auto border-collapse border border-blue-800 w-full">
                        <thead>
                            <tr class="bg-blue-50">
                                <th class="p-1">Envoyée le</th>
                                <th class="p-1">Ouvrage</th>
                                <th class="p-1">Parution</th>
                                <th class="p-1">État</th>
                                <th class="p-1">Remarque de l'équipe</th>
                                <th class="p-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($mine as $p)
                                <tr class="border border-blue-500">
                                    <td class="py-1 px-2 border border-blue-500">{{ $p->created_at?->format('Y-m-d H:i') }}</td>
                                    <td class="py-1 px-2 border border-blue-500">{{ $p->title }} <span class="text-sm italic text-red-700">{{ $p->publisher_name }}</span></td>
                                    <td class="py-1 px-2 border border-blue-500 font-mono">{{ $p->approximate_parution }}</td>
                                    <td class="py-1 px-2 border border-blue-500">{{ $p->status->getLabel() }}</td>
                                    <td class="py-1 px-2 border border-blue-500">{{ $p->review_note }}</td>
                                    <td class="py-1 px-2 border border-blue-500">
                                        @if ($p->isPending())
                                            <form method="POST" action="/admin/formulaires/proposer-publication/{{ $p->id }}/retirer" onsubmit="return confirm('Retirer cette proposition ?')">
                                                @csrf
                                                <button type="submit" class="bg-gray-500 text-white px-2 rounded">Retirer</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
