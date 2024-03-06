<x-app-layout>
    <!-- Page d'accueil administration (jetstream) -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {!! __('Administration BDFI &rarr; Formulaires &rarr; Vérifier les programmes échus') !!}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                A VENIR
                Ici, il faut soit valider la publication (elle est bien parue à la date indiquée), soit la valider en ajustant la date de publication indiquée, soit la postponer (repousser la date), soit la repousser sans date (0000-00-00), soit définitivement le tagger 'jamais paru'.


                {{ html()->form('PUT', '/update-url')->open() }}
                <div>
                    Checkbox : {{ html()->checkbox($name = 'checkbox', $checked = false, $value = '1') }}
                </div>
                <div>
                    Email : {{ html()->email($name = "EMAIL", $value = "email") }}
                </div>
                <div>
                    Input : {{ html()->input($type = null, $name = "INPUT", $value = "input") }}
                </div>
                <div>
                    Fieldset : {{ html()->fieldset($legend = "kjhk dfjlgsfkljgh dfkljgh sdlfkjh ")->class("bg-red-600") }}
                </div>
                <div>
                    Hidden : {{ html()->hidden($name = "HIDDEN", $value = "hidden") }}
                </div>
                <div>
                    Label : {{ html()->label($contents = "Label contents", $for = "for") }}
                </div>
                <div>
                    Legend : {{ html()->legend($contents = "Legend contents") }}
                </div>
                <div>
                    Multiselect : {{ html()->multiselect($name = "MULTISELECT", $options = ['un', 'deux','trois'], $value = "multiselect") }}
                </div>
                <div>
                    Option : {{ html()->option($text = "Option text", $value = "option", $selected = false) }}
                </div>
                <div>
                    Password : {{ html()->password($name = "PASSWORD") }}
                </div>
                <div>
                    Radio 1.1 : {{ html()->radio($name = "RADIO", $checked = false, $value = "radio") }}
                    Radio 1.2 : {{ html()->radio($name = "RADIO", $checked = false, $value = "radio") }}
                    Radio 2.1 : {{ html()->radio($name = "RADIOB", $checked = false, $value = "radio") }}
                    Radio 2.2 : {{ html()->radio($name = "RADIOB", $checked = false, $value = "radio") }}
                </div>
                <div>
                    Select : {{ html()->select($name = "SELECT", $options = ['un', "deux"], $value = "select") }}
                </div>
                <div>
                    Select with option function(): {{ html()->select($name = "SELECT", $value = "select")->options('Un')->options('Deux')->options('Trois') }}
                </div>
                <div>
                    Test select suivi de options
                    {{ html()->select($name = "HOP")->open() }}
                    {{ html()->option($text = "Option un", $value = "o1", $selected = false) }}
                    {{ html()->option($text = "Option deux", $value = "o2", $selected = true) }}
                    {{ html()->select($name = "HOP")->close() }}
                </div>
                <div>
                    Submit : {{ html()->submit($text = "submit") }}
                </div>
                <div>
                    Text : {{ html()->text($name = "TEXT", $value = "text") }}
                </div>
                <div>
                    Textarea : {{ html()->textarea($name = "TEXTAREA", $value = "textarea") }}
                </div>
                <div>
                    Token : {{ html()->token() }}
                </div>

                {{ html()->form()->close() }}
            </div>
        </div>
    </div>
</x-app-layout>


</div>