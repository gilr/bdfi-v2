<x-app-layout>
    <!-- Page d'accueil administration (jetstream) -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="/admin">Administration BDFI</a> &rarr;
            <a href="/admin/formulaires">Formulaires</a> &rarr;
            Ajout rapide auteur
        </h2>
    </x-slot>

    <x-admin.discovery-notice :allowed="auth()->user()->hasMemberRole()" />

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="border border-blue-800 rounded-md p-1 m-1">
                Ajout d'un auteur non déjà en base BDFI.
                <livewire:author-search intro="Si besoin, (re-)vérifier l'absence" label="Recherche auteur" />
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

            {{-- Doublon possible : auteurs de même nom et prénom déjà en base --}}
            @if (session('duplicates'))
                <div class="p-2">
                    <div class="p-2 bg-yellow-100 border border-yellow-600 rounded">
                        <div class="font-semibold">Auteur(s) déjà en base avec ce nom et ce prénom :</div>
                        <ul class="list-disc pl-6 text-sm">
                            @foreach (session('duplicates') as $d)
                                <li>
                                    <x-front.lien-standard link="/auteurs/{{ $d['slug'] }}">{{ $d['label'] }}</x-front.lien-standard>
                                    (#{{ $d['id'] }}, {{ $d['dates'] }}) —
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
                        Auteur ajouté : {{ $label }} (#{{ $id }})
                    </div>
                    <div class="p-2">
                        Si vous avez besoin de mettre à jour l'auteur, c'est ici :
                         <a class="text-red-700" href="/filament/{{ $filament }}/{{ $id }}" target="_blank">fiche {{ $area }} <span class="font-bold"> {{ $id }}</span></a> (s'ouvre dans un nouvel onglet).<br />
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
            <form method="POST" action="/admin/formulaires/ajout-auteur" class="bg-white shadow-xl rounded p-4 m-1">
                @csrf

                {{-- Nom + Prénom : le doublon se vérifie sur la PAIRE ; avertissement si le nom est entièrement
                     en majuscules (ex. "MASTERTON" au lieu de "Masterton") --}}
                <livewire:duplicate-name-check
                    model="App\Models\Author"
                    field-name="name"
                    label="Nom *"
                    column="name"
                    noun="un auteur nommé"
                    front-link="/auteurs/"
                    placeholder="Nom de l'auteur"
                    help="« Poe », « Levilain-Clément », « La Motte-Fouqué », « Balzac » (sans le « de »)"
                    css-class="w-64 border rounded bg-yellow-100"
                    maxlength="32"
                    :uppercase-warning="true"
                    second-field-name="first_name"
                    second-label="Prénom(s)"
                    second-column="first_name"
                    second-placeholder="Prénoms"
                    second-help="Vide pour un pseudonyme ou un collectif sans prénom"
                    second-css-class="w-64 border rounded bg-yellow-100"
                    second-maxlength="32"
                    :old-value="old('name')"
                    :old-second-value="old('first_name')"
                />

                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <label for="country_id" class="w-32 font-semibold">Nationalité</label>
                    <select id="country_id" name="country_id" class="w-64 border rounded bg-yellow-100">
                        <option value="{{ $unknownCountryId }}">Inconnue</option>
                        <optgroup label="Les plus fréquentes">
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
                    <label for="gender" class="w-32 font-semibold">H/F & co</label>
                    <select id="gender" name="gender" class="w-64 border rounded bg-yellow-100">
                        @foreach (App\Enums\AuthorGender::cases() as $gender)
                            <option value="{{ $gender->value }}" @selected(old('gender') === $gender->value)>{{ $gender->getLabel() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <label for="is_pseudonym" class="w-32 font-semibold">Pseudonyme</label>
                    <input type="checkbox" id="is_pseudonym" name="is_pseudonym" value="1" @checked(old('is_pseudonym'))>
                    <span class="text-xs text-gray-500">Les liens avec le ou les noms réels se font ensuite - si connus - dans Filament</span>
                </div>

                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <label for="birth_date" class="w-32 font-semibold">Naissance</label>
                    <input id="birth_date" name="birth_date" value="{{ old('birth_date') }}" maxlength="10" class="w-32 border rounded bg-yellow-100"
                           placeholder="AAAA-MM-JJ">
                    <label for="date_death" class="font-semibold ml-2">Décès</label>
                    <input id="date_death" name="date_death" value="{{ old('date_death') }}" maxlength="10" class="w-32 border rounded bg-yellow-100"
                           placeholder="AAAA-MM-JJ">
                    <span class="text-xs text-gray-500">AAAA-MM-JJ, 00 si mois ou jour inconnu (1983-00-00), AAAA-circa ou -AAA-circa ; laisser vide une date inconnue</span>
                </div>

                {{-- Homonyme : proposé seulement après la détection d'un auteur de même nom et prénom --}}
                @if (session('duplicates') || old('homonym'))
                    <div class="flex flex-wrap items-center gap-2 mb-1 p-1 bg-yellow-100 rounded">
                        <label for="homonym" class="w-32 font-semibold">Homonyme</label>
                        <input type="checkbox" id="homonym" name="homonym" value="1" @checked(old('homonym'))>
                        <span class="text-sm">Créer quand même : il s'agit d'un autre auteur portant le même nom</span>
                    </div>
                @endif

                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <button type="submit" @disabled(! auth()->user()->hasMemberRole())  class="bg-blue-400 font-semibold border border-blue-800 rounded px-4 py-2 disabled:opacity-50 disabled:cursor-not-allowed">Créer l'auteur</button>
                    <span class="text-xs text-gray-500">* obligatoire. Les autres informations (biographie, lieux, noms réels...) se complètent ensuite dans Filament.</span>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
