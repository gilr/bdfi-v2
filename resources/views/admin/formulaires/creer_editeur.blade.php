<x-app-layout>
    <!-- Création rapide d'un éditeur -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="/admin">Administration BDFI</a> &rarr;
            <a href="/admin/formulaires">Formulaires</a> &rarr;
            Ajout rapide éditeur
        </h2>
    </x-slot>

    <x-admin.discovery-notice :allowed="auth()->user()->hasMemberRole()" />

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="border border-blue-800 rounded-md p-1 m-1">
                Ajout d'un éditeur nouveau ou non déjà en base. <span class="font-semibold">Attention</span>, pas de compte d'auteur SVP.
                <livewire:publisher-search intro="Si besoin, (re-)vérifier l'absence" label="Recherche éditeur" />
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

            {{-- Doublon possible : éditeur de même nom déjà en base --}}
            @if (session('duplicates'))
                <div class="p-2">
                    <div class="p-2 bg-yellow-100 border border-yellow-600 rounded">
                        <div class="font-semibold">Éditeur(s) déjà en base avec ce nom :</div>
                        <ul class="list-disc pl-6 text-sm">
                            @foreach (session('duplicates') as $d)
                                <li>
                                    <x-front.lien-standard link="/editeurs/{{ $d['slug'] }}">{{ $d['label'] }}</x-front.lien-standard>
                                    (#{{ $d['id'] }}, créé en {{ $d['dates'] }}) —
                                    <a class="text-red-700" href="/filament/{{ $filament }}/{{ $d['id'] }}" target="_blank">fiche Filament</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if (!empty($status))
                <div class="p-2">
                    <div class="p-2 text-xl border-b border-green-800 bg-green-100">
                        Éditeur créé : {{ $label }} (#{{ $id }})
                    </div>
                    <div class="p-2">
                        Page de l'éditeur : <x-front.lien-standard link="/editeurs/{{ $slug }}">{{ $label }}</x-front.lien-standard>
                        — fiche à compléter : <a class="text-red-700" href="/filament/{{ $filament }}/{{ $id }}" target="_blank">fiche {{ $area }} <span class="font-bold">{{ $id }}</span></a>
                        (nouvel onglet : sigle BDFI, autres informations…).
                    </div>
                    <div class="p-2 bg-green-100">
                        Vous pouvez en créer un autre :
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="/admin/formulaires/ajout-editeur" class="bg-white shadow-xl rounded p-4 m-1">
                @csrf

                <livewire:duplicate-name-check
                    model="App\Models\Publisher"
                    field-name="name"
                    label="Nom *"
                    column="name"
                    noun="un éditeur nommé"
                    front-link="/editeurs/"
                    placeholder="Nom de l'éditeur"
                    help="Le nom officiel ou principal (même s'il a évolué au fil du temps)"
                    :old-value="old('name')"
                />

                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <label for="year_start" class="w-32 font-semibold">Création *</label>
                    <input id="year_start" name="year_start" value="{{ old('year_start') }}" maxlength="4" required class="w-24 border rounded bg-yellow-100 font-mono"
                           placeholder="2024">
                    <span class="text-xs text-gray-500">Année de création ; mettre 0 si temporairement inconnue</span>
                </div>

                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <label for="country_id" class="w-32 font-semibold">Pays *</label>
                    <select id="country_id" name="country_id" class="w-64 border rounded bg-yellow-100">
                        <option value="{{ $unknownCountryId }}">Inconnu</option>
                        <optgroup label="Les plus fréquents">
                            @foreach ($countries['frequent'] as $c)
                                <option value="{{ $c->id }}" @selected(old('country_id') == $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Tous les pays">
                            @foreach ($countries['others'] as $c)
                                <option value="{{ $c->id }}" @selected(old('country_id') == $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>

                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <label for="type" class="w-32 font-semibold">Type *</label>
                    <select id="type" name="type" class="w-64 border rounded bg-yellow-100">
                        @foreach (App\Enums\PublisherType::cases() as $t)
                            <option value="{{ $t->value }}" @selected(old('type', App\Enums\PublisherType::EDITEUR->value) === $t->value)>{{ $t->getLabel() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <label for="alt_names" class="w-32 font-semibold">Autres noms</label>
                    <input id="alt_names" name="alt_names" value="{{ old('alt_names') }}" maxlength="512" class="w-full border rounded bg-yellow-100 max-w-xl"
                           placeholder="Autres formes du nom, séparées par des virgules">
                </div>

                {{-- Homonyme : proposé seulement après la détection d'un éditeur de même nom --}}
                @if (session('duplicates') || old('homonym'))
                    <div class="flex flex-wrap items-center gap-2 mb-1 p-1 bg-yellow-100 rounded">
                        <label for="homonym" class="w-32 font-semibold">Homonyme</label>
                        <input type="checkbox" id="homonym" name="homonym" value="1" @checked(old('homonym'))>
                        <span class="text-sm">Créer quand même : il s'agit d'un autre éditeur portant le même nom</span>
                    </div>
                @endif

                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <button type="submit" @disabled(! auth()->user()->hasMemberRole())  class="bg-blue-400 font-semibold border border-blue-800 rounded px-4 py-2 disabled:opacity-50 disabled:cursor-not-allowed">Créer l'éditeur</button>
                    <span class="text-xs text-gray-500">* obligatoire. Les autres informations (sigle BDFI, adresse...) se complètent ensuite dans Filament.</span>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
