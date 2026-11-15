<x-app-layout>
    <!-- Création rapide d'une collection -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="/admin">Administration BDFI</a> &rarr;
            <a href="/admin/formulaires">Formulaires</a> &rarr;
            Ajout rapide collection
        </h2>
    </x-slot>

    <x-admin.discovery-notice :allowed="auth()->user()->hasMemberRole()" />

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="border border-blue-800 rounded-md p-1 m-1">
                Ajout de collection, groupe, revue, fanzine, magazine, journal ou anthologie périodique, non déjà recensée.
                <livewire:collection-search intro="Si besoin, (re-)vérifier l'absence" label="Recherche collection" />
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

            {{-- Doublon possible : collection de même nom déjà en base --}}
            @if (session('duplicates'))
                <div class="p-2">
                    <div class="p-2 bg-yellow-100 border border-yellow-600 rounded">
                        <div class="font-semibold">Collection(s) déjà en base avec ce nom :</div>
                        <ul class="list-disc pl-6 text-sm">
                            @foreach (session('duplicates') as $d)
                                <li>
                                    <x-front.lien-standard link="/collections/{{ $d['slug'] }}">{{ $d['label'] }}</x-front.lien-standard>
                                    (#{{ $d['id'] }}, créée en {{ $d['dates'] }}) —
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
                        Collection créée : {{ $label }} (#{{ $id }})
                    </div>
                    <div class="p-2">
                        Page de la collection : <x-front.lien-standard link="/collections/{{ $slug }}">{{ $label }}</x-front.lien-standard>
                        — fiche à compléter : <a class="text-red-700" href="/filament/{{ $filament }}/{{ $id }}" target="_blank">fiche {{ $area }} <span class="font-bold">{{ $id }}</span></a>
                        (nouvel onglet : 2ᵉ et 3ᵉ éditeur éventuels, sigle BDFI, autres informations…).
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
            <form method="POST" action="/admin/formulaires/ajout-collection" class="bg-white shadow-xl rounded p-4 m-1">
                @csrf

                <livewire:duplicate-name-check
                    model="App\Models\Collection"
                    field-name="name"
                    label="Nom *"
                    column="name"
                    noun="une collection nommée"
                    front-link="/collections/"
                    placeholder="Nom de la collection"
                    help="Nom complet, par exemple « La Loupe » ; pour une sous-collection, l'intégrer : « La Loupe, série épouvante »"
                    :old-value="old('name')"
                />

                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <label for="shortname" class="w-32 font-semibold">Nom court</label>
                    <input id="shortname" name="shortname" value="{{ old('shortname') }}" maxlength="128" class="w-full border rounded bg-yellow-100 max-w-xl"
                           placeholder="Laisser vide pour reprendre le nom">
                    <span class="text-xs text-gray-500">Sous-collection seule, sans le nom de la collection mère : « série épouvante ». Vide = identique au nom.</span>
                </div>

                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <label for="year_start" class="w-32 font-semibold">Création *</label>
                    <input id="year_start" name="year_start" value="{{ old('year_start') }}" maxlength="4" required class="w-24 border rounded bg-yellow-100 font-mono"
                           placeholder="2024">
                    <span class="text-xs text-gray-500">Année de création ; mettre 0 si temporairement inconnue</span>
                </div>

                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <label for="type" class="w-32 font-semibold">Type *</label>
                    <select id="type" name="type" class="w-48 border rounded bg-yellow-100">
                        @foreach (App\Enums\CollectionType::cases() as $t)
                            <option value="{{ $t->value }}" @selected(old('type', App\Enums\CollectionType::COLLECTION->value) === $t->value)>{{ $t->getLabel() }}</option>
                        @endforeach
                    </select>
                    <label for="support" class="font-semibold ml-2">Support *</label>
                    <select id="support" name="support" class="w-48 border rounded bg-yellow-100">
                        @foreach (App\Enums\CollectionSupport::cases() as $t)
                            <option value="{{ $t->value }}" @selected(old('support', App\Enums\CollectionSupport::PAPIER->value) === $t->value)>{{ $t->getLabel() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <label for="periodicity" class="w-32 font-semibold">Périodicité *</label>
                    <select id="periodicity" name="periodicity" class="w-48 border rounded bg-yellow-100">
                        @foreach (App\Enums\CollectionPeriodicity::cases() as $t)
                            <option value="{{ $t->value }}" @selected(old('periodicity', App\Enums\CollectionPeriodicity::NA->value) === $t->value)>{{ $t->getLabel() }}</option>
                        @endforeach
                    </select>
                    <span class="text-xs text-gray-500">« Non applicable » pour une collection ordinaire ; sinon la périodicité de la revue, du fanzine...</span>
                </div>

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

                {{-- Homonyme : proposé seulement après la détection d'une collection de même nom --}}
                @if (session('duplicates') || old('homonym'))
                    <div class="flex flex-wrap items-center gap-2 mb-1 p-1 bg-yellow-100 rounded">
                        <label for="homonym" class="w-32 font-semibold">Homonyme</label>
                        <input type="checkbox" id="homonym" name="homonym" value="1" @checked(old('homonym'))>
                        <span class="text-sm">Créer quand même : il s'agit d'une autre collection portant le même nom</span>
                    </div>
                @endif

                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <button type="submit" @disabled(! auth()->user()->hasMemberRole())  class="bg-blue-400 font-semibold border border-blue-800 rounded px-4 py-2 disabled:opacity-50 disabled:cursor-not-allowed">Créer la collection</button>
                    <span class="text-xs text-gray-500">* obligatoire. L'éditeur et les autres informations se complètent ensuite dans Filament.</span>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
