<x-app-layout>
    <!-- Page d'accueil administration (jetstream) -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {!! __('Administration BDFI &rarr; Formulaires &rarr; Ajout rapide collection') !!}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="border border-blue-800 rounded-lg p-2 hidden sm:block my-2">
                Ajout de collection, groupe, revue, fanzine, magazine, journal ou anthologie périodique.
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
                        Collection créée !
                    </div>
                    <div class="p-2">
                        Si vous avez besoin de mettre à jour la collection, c'est ici :
                         <a class="text-red-700" href="/filament/{{ $filament }}/{{ $id }}" target="_blank">fiche {{ $area }} <span class="font-bold"> {{ $id }}</span></a> (s'ouvre dans un nouvel onglet).<br />
                    </div>
                    <div class="p-2 bg-green-100">
                        Vous pouvez en créer une autre :
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{ html()->form('POST', '/admin/formulaires/ajout-collection')->open() }}
            <div class="p-2 text-2xl border-b border-yellow-800">
                Informations indispensables :
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Nom :
                {{ html()->text($name = "name")->size(80)->class("m-2 bg-yellow-100")->placeholder("Nom de la collection") }}
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Le nom complet de la collection (exemple, "La Loupe") ou de la sous-collection qui doit alors intégrer le nom de la collection (par exemple "La Loupe, série épouvante").
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Nom court :
                {{ html()->text($name = "shortname")->size(64)->class("m-2 bg-yellow-100")->placeholder("Si collection, nom sans la collection mère") }}
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Le nom de la sous-collection seule dans le cas d'une sous-collection. Nom identique sinon.
                <br />Exemple, pour la sous-collection de nom "La Loupe, série épouvante", le nom court sera "série épouvante".
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Année de création :
                {{ html()->text($name = "year_start")->size(5)->class("m-2 bg-yellow-100")->class("font-mono")->placeholder("2024") }}
                (Peut temporairement être mis à 0).
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Type d'ensemble :
                {{ html()->select($name = "type")->class("m-2 bg-yellow-100")->open() }}
                @foreach (App\Enums\CollectionType::cases() as $type)
                    {{ html()->option($text = $type->getLabel(), $value = $type->value) }}
                @endforeach
                {{ html()->select($name = "type")->close() }}
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Type de support :
                {{ html()->select($name = "support")->class("m-2 bg-yellow-100")->open() }}
                @foreach (App\Enums\CollectionSupport::cases() as $type)
                    {{ html()->option($text = $type->getLabel(), $value = $type->value) }}
                @endforeach
                {{ html()->select($name = "support")->close() }}
            </div>
            <div class="p-2 text-2xl border-b border-yellow-800">
                Informations optionnelles :
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Les autres informations, optionnelles, pourront être renseignées sur la page filament après création.
                Ne pas oublier de lier à un éditeur existant si besoin (sera ajouté ici plus tard).
                En l'absence de "vrai" éditeur, il est d'ailleurs conseillé de créer un éditeur de nom identique à la collection, et de les lier entre eux.
            </div>
            <div class="p-2 text-xl border-yellow-800">
                {{ html()->submit($text = "Créer la collection")->class("bg-blue-400 font-semibold border border-blue-800 rounded px-4 py-2 m-2") }}
                {{ html()->form()->close() }}
            </div>
        </div>
    </div>
</x-app-layout>


</div>