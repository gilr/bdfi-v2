<x-app-layout>
    <!-- Page d'accueil administration (jetstream) -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {!! __('Administration BDFI &rarr; Outils &rarr; Fb du jour' ) !!}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4">

                <div class="p-2 text-xl">Texte à copier-coller sur Facebook pour aujourd'hui ({{$today}})</div>

                <br /><br />
                <div style='text-align:left; margin: 0 50px 50px 50px; padding: 25px; width:400px; background-color:#ccf; border-radius:3px;'>
                    Aujourd'hui {{ $dateenclair }}, c'est l'anniversaire de :<br /><br />

                    <table>
                        @foreach ($auteurs as $auteur)
                        <tr>
                            <td>{{ substr($auteur->birth_date, 0, 4) }} : 
                                {{ $auteur->first_name }} {{ $auteur->name }}
                                @if (substr($auteur->date_death, 0, 4) !== '0000')
                                (&dagger; {{ substr($auteur->date_death, 0, 4) }})
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </table>

                    <br />C'est aussi la date anniversaire du décès de :<br /><br />

                    <table>
                        @foreach ($auteurs2 as $auteur)
                        <tr>
                            <td>{{ substr($auteur->birth_date, 0, 4) }}-{{ substr($auteur->date_death, 0, 4) }} :
                                {{ $auteur->first_name }} {{ $auteur->name }}
                            </td>
                        </tr>
                        @endforeach
                    </table>
                    <br />Pour en savoir plus sur ces auteurs : http://www.bdfi.net/
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
