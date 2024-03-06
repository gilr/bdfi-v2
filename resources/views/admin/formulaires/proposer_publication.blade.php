<x-app-layout>
    <!-- Page d'accueil administration (jetstream) -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {!! __('Administration BDFI &rarr; Formulaires &rarr; de proposition ajout') !!}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="border border-blue-800 rounded-lg p-2 hidden sm:block my-2">
                Proposition pour ajout d'une publication <span class='font-semibold'>déjà parue</span> et non déjà recensée.
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
                        Proposition faite !
                    </div>
                    <div class="p-2">
                        Les administrateurs vont être prévenus, la proposition d'ajout sera étudiée.<br />
                    </div>
                    <div class="p-2 bg-green-100">
                        Vous pouvez en proposer un autre :
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{ html()->form('POST', '/admin/formulaires/ajout-publication')->open() }}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Etat de parution :
                {{ html()->select($name = "publication_status")->class("m-2 p-2 border-dotted bg-gray-300 text-gray-500")->disabled()->open() }}
                @foreach (App\Enums\publicationStatus::cases() as $type)
                    @if ($type === App\Enums\PublicationStatus::PROPOSE)
                        {{ html()->option($text = $type->getLabel(), $value = $type->value)->selected() }}
                    @else
                        {{ html()->option($text = $type->getLabel(), $value = $type->value) }}
                    @endif
                @endforeach
                {{ html()->select($name = "publication_status")->close() }}
                {{ html()->hidden($name = "publication_status", $value = App\Enums\PublicationStatus::PROPOSE->value) }}
            </div>
            <div class="p-2 text-2xl border-b border-yellow-800">
                Informations indispensables :
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Titre de contenu de l'ouvrage / la publication :
                {{ html()->text($name = "name")->size(80)->class("m-2 bg-yellow-100")->placeholder("Nom de la publication") }}
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Type :
                {{ html()->select($name = "type")->class("m-2 bg-yellow-100")->placeholder('Choisir un contenu')->class("text-gray-400")->open() }}
                @foreach (App\Enums\publicationContent::cases() as $type)
                    {{ html()->option($text = $type->getLabel(), $value = $type->value)->class("text-black") }}
                @endforeach
                {{ html()->select($name = "type")->close() }}
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Support :
                {{ html()->select($name = "support")->class("m-2 bg-yellow-100")->placeholder('Choisir un support')->class("text-gray-400")->open() }}
                @foreach (App\Enums\publicationSupport::cases() as $type)
                    {{ html()->option($text = $type->getLabel(), $value = $type->value)->class("text-black") }}
                @endforeach
                {{ html()->select($name = "support")->close() }}
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Date de parution :
                {{ html()->text($name = "approximate_parution")->size(10)->class("m-2 bg-yellow-100")->class("font-mono")->placeholder("2024-00-00") }}
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
                Genre général ouvrage :
                {{ html()->select($name = "genre_stat")->class("m-2 bg-yellow-100")->placeholder('Choisir le genre')->class("text-gray-400")->open() }}
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
                Public cible :
                {{ html()->select($name = "target_audience")->class("m-2 bg-yellow-100")->open() }}
                @foreach (App\Enums\AudienceTarget::cases() as $type)
                    {{ html()->option($text = $type->getLabel(), $value = $type->value) }}
                @endforeach
                {{ html()->select($name = "target_audience")->close() }}
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Notes pour la validation (infos, liens, sources...) :<br />
                {{ html()->textarea($name = "private")->class("m-2 bg-yellow-100 font-mono w-11/12") }}
            </div>
            <div class="p-2 text-2xl border-b border-yellow-800">
                Informations optionnelles :
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                ISBN :
                {{ html()->text($name = "isbn")->size(18)->class("m-2 bg-yellow-100 font-mono") }}
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                Infos format physique :
                Ouvrage relié : {{ html()->checkbox($name = "is_hardcover", $checked = false, $value = '0')->class("m-2 bg-yellow-200 font-mono") }}
                Avec jaquette : {{ html()->checkbox($name = "has_dustjacket", $checked = false, $value = '0')->class("m-2 bg-yellow-200 font-mono") }}
                Couverture à rabat : {{ html()->checkbox($name = "has_coverflaps", $checked = false, $value = '0')->class("m-2 bg-yellow-200 font-mono") }}
            </div>
            <div class="p-2 text-xl border-yellow-800">
                {{ html()->submit($text = "Faire la proposition")->class("bg-blue-400 font-semibold border border-blue-800 rounded px-4 py-2 m-2") }}
                {{ html()->form()->close() }}
            </div>
        </div>
    </div>
</x-app-layout>


</div>