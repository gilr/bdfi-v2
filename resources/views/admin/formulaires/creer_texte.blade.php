<x-app-layout>
    <!-- Page d'accueil administration (jetstream) -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="/admin">Administration BDFI</a> &rarr;
            <a href="/admin/formulaires">Formulaires</a> &rarr;
            Ajout rapide oeuvre
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="border border-blue-800 rounded-lg p-2 hidden sm:block my-2">
                Ajout d'une oeuvre publiée et non déjà recensée.
                <livewire:title-search intro="Si besoin, (re-)vérifier l'absence" label="Recherche oeuvre" />
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
            @if (isset($status))
                <div class="p-2">
                    <div class="p-2 text-xl border-b border-green-800 bg-green-100">
                        Oeuvre ajoutée !
                    </div>
                    <div class="p-2">
                        Pour mettre à jour l'oeuvre, c'est ici :
                         <a class="text-red-700" href="/filament/{{ $filament }}/{{ $id }}" target="_blank">fiche {{ $area }} <span class="font-bold"> {{ $id }}</span></a> (s'ouvre dans un nouvel onglet). Entre autres, il faut en général également attacher un ou plusieurs auteurs. Certaines de ces étapes seront rapatriées dans cette zone, mais en attendant, il faut passer par les tables.<br />
                    </div>
                    <div class="p-2">
                        Pour créer la publication qui le contient :
                        <x-admin.link lien='ajout-publication?tid={{ $id }}'>Ajout publication parue non déjà recensée</x-admin.link>
                    </div>
                    <div class="font-semibold p-2 bg-green-100">
                        Vous pouvez en créer un autre :
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{ html()->form('POST', '/admin/formulaires/ajout-texte')->open() }}
            <div class="p-2 text-2xl border-b border-yellow-800">
                Informations indispensables :
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Titre (francophone) de l'oeuvre :
                {{ html()->text($name = "name")->size(80)->class("m-2 bg-yellow-100")->placeholder("Nom de l'oeuvre / du texte") }}
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Type :
                {{ html()->select($name = "type")->class("m-2 bg-yellow-100")->placeholder('Choisir un type')->class("text-gray-800")->open() }}
                @foreach (App\Enums\TitleType::cases() as $type)
                    {{ html()->option($text = $type->getLabel(), $value = $type->value)->class("text-black") }}
                @endforeach
                {{ html()->select($name = "type")->close() }}
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Date de copyright :
                {{ html()->text($name = "copyright")->size(10)->class("m-2 bg-yellow-100")->class("font-mono")->placeholder("2024-00-00") }}
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Format 'AAAA-MM-00' (exemple : 1983-05-00, pour mai 1983). 'AAAA-00-00' si l'année seule est connue, ou éventuellement 'AAAA-MM-JJ' si le jour est donné par l'éditeur. Si approximative, utiliser "1870-circa".
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Appartenance aux genres référencés :
                {{ html()->select($name = "is_genre")->class("m-2 bg-yellow-100")->open() }}
                @foreach (App\Enums\GenreAppartenance::cases() as $type)
                    {{ html()->option($text = $type->getLabel(), $value = $type->value) }}
                @endforeach
                {{ html()->select($name = "is_genre")->close() }}
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Genre général oeuvre :
                {{ html()->select($name = "genre_stat")->class("m-2 bg-yellow-100")->placeholder('Choisir le genre')->class("text-gray-800")->open() }}
                @foreach (App\Enums\GenreStat::cases() as $type)
                    {{ html()->option($text = $type->getLabel(), $value = $type->value)->class("text-black") }}
                @endforeach
                {{ html()->select($name = "genre_stat")->close() }}
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Public cible :
                {{ html()->select($name = "target_audience")->class("m-2 bg-yellow-100")->open() }}
                @foreach (App\Enums\AudienceTarget::cases() as $type)
                    {{ html()->option($text = $type->getLabel(), $value = $type->value) }}
                @endforeach
                {{ html()->select($name = "target_audience")->close() }}
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Les autres informations pourront être renseignées sur la page filament après création.
                {{ html()->hidden($name = "private", $value="") }}
            </div>
            <div class="p-2 text-xl border-yellow-800">
                {{ html()->submit($text = "Créer l'oeuvre")->class("bg-blue-400 font-semibold border border-blue-800 rounded px-4 py-2 m-2") }}
                {{ html()->form()->close() }}
            </div>
        </div>
    </div>
</x-app-layout>
