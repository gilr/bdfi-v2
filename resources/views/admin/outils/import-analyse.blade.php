<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="/admin">Administration BDFI</a> &rarr;
            <a href="/admin/outils">Outils</a> &rarr;
            Analyse d'un fichier d'import
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="border border-blue-800 rounded-lg p-1 m-1">
                <div class="hidden sm:block">
                    Équivalent de <code>php artisan bdfi:import-books &lt;fichier&gt; --analyse</code> :
                    conversion, validation et simulation de l'import, <b>sans aucune écriture en base</b>.
                </div>
                <livewire:book-import-analysis />
                <div class="hidden sm:block text-sm">
                    <b>Avertissements et restrictions :</b>
                    <br />- Il est possible d'analyser ou d'importer des fichiers JSON, COL ou CSV
                    <br />- Fichiers de {{ \App\Livewire\BookImportAnalysis::MAX_BOOKS }} livres au plus
                    ({{ \App\Livewire\BookImportAnalysis::MAX_FILE_KB }} Ko max) : pour un fichier plus gros, le découper en morceaux.
                    <br />- Comme la commande, l'analyse écrit les fichiers &lt;nom&gt;.converted.json et &lt;nom&gt;.to-add.json à côté du fichier source.
                    <br />- Les fichiers téléversés sont rangés dans storage/app/import/web/ et supprimés après
                    {{ \App\Livewire\BookImportAnalysis::UPLOAD_RETENTION_DAYS }} jours (avec les fichiers générés).
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
