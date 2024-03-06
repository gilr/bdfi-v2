<x-app-layout>
    <!-- Page d'accueil administration (jetstream) -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {!! __('Administration BDFI &rarr; Formulaires &rarr; Ajout rapide éditeur') !!}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="border border-blue-800 rounded-lg p-2 hidden sm:block my-2">
                Ajout d'un éditeur nouveau ou inconnu de BDFI. <span class="font-semibold">Attention</span>, pas de compte d'auteur SVP.
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
                        Editeur créé !
                    </div>
                    <div class="p-2">
                        Si vous avez besoin de mettre à jour l'éditeur, c'est ici :
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
            {{ html()->form('POST', '/admin/formulaires/ajout-editeur')->open() }}
            <div class="p-2 text-2xl border-b border-yellow-800">
                Informations indispensables :
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Nom :
                {{ html()->text($name = "name")->size(80)->class("m-2 bg-yellow-100")->placeholder("Nom de l'éditeur") }}
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Le nom officiel ou principal (si à évolué au cours du temps) de l'éditeur.
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Année de création :
                {{ html()->text($name = "year_start")->size(5)->class("m-2 bg-yellow-100")->class("font-mono")->placeholder("2024") }}
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Peut temporairement être mis à 0.
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Pays :
                {{ html()->select($name = "pays")->class("m-2 bg-yellow-100")->open() }}
                {{ html()->option($text = "France", $value = 'France') }}
                {{ html()->option($text = "Canada", $value = 'Canada') }}
                {{ html()->option($text = "Suisse", $value = 'Suisse') }}
                {{ html()->option($text = "Belgique", $value = 'Belgique') }}
                {{ html()->option($text = "Luxembourg", $value = 'Luxembourg') }}
                {{ html()->select($name = "pays")->close() }}
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <span class="font-semibold">Attention</span>, choix volontairement limité aux principaux pays francophone. Si autre, créer avec 'France' puis modifier ensuite en page filament.
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Type d'éditeur :
                {{ html()->select($name = "type")->class("m-2 bg-yellow-100")->open() }}
                @foreach (App\Enums\PublisherType::cases() as $type)
                    {{ html()->option($text = $type->getLabel(), $value = $type->value) }}
                @endforeach
                {{ html()->select($name = "type")->close() }}
            </div>
            <div class="p-2 text-2xl border-b border-yellow-800">
                Informations optionnelles :
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Autres dénominations :
                {{ html()->text($name = "alt_names")->size(256)->class("m-2 bg-yellow-100")->placeholder("Autres formes du nom, séparées par des virgules") }}
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Les autres informations optionnelles pourront être renseignées sur la page filament après création.
            </div>
            <div class="p-2 text-xl border-yellow-800">
                {{ html()->submit($text = "Créer l'éditeur")->class("bg-blue-400 font-semibold border border-blue-800 rounded px-4 py-2 m-2") }}
                {{ html()->form()->close() }}
            </div>
        </div>
    </div>
</x-app-layout>


</div>