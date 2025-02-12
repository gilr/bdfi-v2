<x-app-layout>
    <!-- Page d'accueil administration (jetstream) -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {!! __('Administration BDFI &rarr; Formulaires &rarr; Ajout rapide auteur') !!}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="border border-blue-800 rounded-lg p-1 m-1">
                <div class="hidden sm:block">
                    Ajout d'un auteur non déjà en base BDFI.
                </div>
                <livewire:author-search intro="Si besoin (c'est mieux !), vérifier l'existence" label="Recherche auteur" />
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
                        Auteur ajouté !
                    </div>
                    <div class="p-2">
                        Si vous avez besoin de mettre à jour l'auteur, c'est ici :
                         <a class="text-red-700" href="/filament/{{ $filament }}/{{ $id }}" target="_blank">fiche {{ $area }} <span class="font-bold"> {{ $id }}</span></a> (s'ouvre dans un nouvel onglet).<br />
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
            {{ html()->form('POST', '/admin/formulaires/ajout-auteur')->open() }}
            <div class="p-2 text-2xl border-b border-yellow-800">
                Informations indispensables :
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Nom :
                {{ html()->text($name = "name")->size(80)->class("m-2 bg-yellow-100")->placeholder("Nom de l'auteur") }}
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Forme classique "Nom", exemple "Poe", "Levilain-Clément", "La Motte-Fouqué", "Balzac" (sans le "de")...
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Prénom(s) :
                {{ html()->text($name = "first_name")->size(80)->class("m-2 bg-yellow-100")->placeholder("Prénoms") }}
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Nationalité :
                {{ html()->select($name = "pays")->class("m-2 bg-yellow-100")->open() }}
                {{ html()->option($text = "Inconnu", $value = '?') }}
                {{ html()->option($text = "Etats-Unis", $value = 'Etats-Unis') }}
                {{ html()->option($text = "France", $value = 'France') }}
                {{ html()->option($text = "Royaume Uni", $value = 'Royaume Uni') }}
                {{ html()->option($text = "Canada", $value = 'Canada') }}
                {{ html()->select($name = "pays")->close() }}
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <span class="font-semibold">Attention</span>, choix volontairement limité à quelque principaux pays. Si autre, créer avec 'Inconnu' puis modifier ensuite en page filament.
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                H/F & co :
                {{ html()->select($name = "gender")->class("m-2 bg-yellow-100")->open() }}
                @foreach (App\Enums\AuthorGender::cases() as $gender)
                    {{ html()->option($text = $gender->getLabel(), $value = $gender->value) }}
                @endforeach
                {{ html()->select($name = "gender")->close() }}
            </div>
            <div class="p-2 text-2xl border-b border-yellow-800">
                Informations optionnelles :
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Date de naissance :
                {{ html()->text($name = "birth_date", $value = "0000-00-00")->size(10)->class("m-2 bg-yellow-100")->placeholder("AAAA-MM-JJ") }}
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Format 'AAAA-MM-JJ' (exemple : 1983-05-19). 'AAAA-00-00' si l'année seule est connue, et vide ou '0000-00-00' si la date est inconnue. Les formats '1410-circa' ou '-500-circa' sont également acceptés.
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Les autres informations optionnelles pourront être renseignées sur la page filament après création.
            </div>
            <div class="p-2 text-xl border-yellow-800">
                {{ html()->submit($text = "Créer l'auteur")->class("bg-blue-400 font-semibold border border-blue-800 rounded px-4 py-2 m-2") }}
                {{ html()->form()->close() }}
            </div>
        </div>
    </div>
</x-app-layout>
