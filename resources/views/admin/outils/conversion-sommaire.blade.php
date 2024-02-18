<x-app-layout>
    <!-- Page d'accueil administration (jetstream) -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {!! __('Administration BDFI &rarr; Outils &rarr; Conversion fichiers locaux' ) !!}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4">

                <h2>Outil interne de transformation de format de sommaire (béta)</h2>

                {!! Form::model($data, ['method' => 'POST', 'action' => ['ToolController@postConvertContent'], 'class' => 'form-horizontal']) !!}

                <div class="form-group">
                    {!! Form::label('labelcontent0', 'Contenu de départ : ', ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-10">
                        {!! Form::textarea('content0', null, ['class' => 'form-control']) !!}
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('labelformat', 'Formats : ', ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-4">
                        Départ : {!! Form::select('format0', array(
                        'tvpn' => 'titre, prénom nom',
                        'tvdpn' => 'titre, de prénom nom',
                        'tvnp' => 'titre, nom prénom',
                        'tvdnp' => 'titre, de nom prénom',
                        'npvt' => 'nom prénom, titre',
                        'pnvt' => 'prénom nom, titre'),
                        'tvdpn') !!}
                    </div>
                    <div class="col-sm-2">&rarr; &rarr; &rarr; &rarr;</div>
                    <div class="col-sm-4">
                        Arrivée : {!! Form::select('format1', array(
                        'tvpn' => 'titre, prénom nom',
                        'tvnp' => 'titre, nom prénom',
                        'npvt' => 'nom prénom, titre',
                        'pnvt' => 'prénom nom, titre',
                        'npt' => 'NOM;prénom;titre',
                        'gil' => 'Base DOS (:...NOM prénom     titre)'),
                        'gil') !!}
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-3">
                        {!! Form::submit('Convertir', ['class' => 'btn btn-primary form-control']) !!}
                    </div>    
                </div>
                <div class="form-group">
                    {!! Form::label('labelcontent1', 'Contenu converti : ', ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-10">
                        {!! Form::textarea('content1', null, ['class' => 'form-control']) !!}
                    </div>
                </div>
                {!! Form::close() !!}

                @if ($errors->any())
                <ul class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
