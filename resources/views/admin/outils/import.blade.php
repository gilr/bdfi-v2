<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="/admin">Administration BDFI</a> &rarr;
            <a href="/admin/outils">Outils</a> &rarr;
            Importer un fichier
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="border border-blue-800 rounded-lg p-1 m-1">
                <div class="hidden sm:block">
                    Équivalent de <code>php artisan bdfi:import-books &lt;fichier&gt; [--dry-run]</code>.
                    Lancer d'abord l'<x-admin.link lien='/admin/outils/import-analyse'>analyse du fichier</x-admin.link>,
                    puis importer de préférence le fichier <code>&lt;nom&gt;.to-add.json</code> qu'elle produit.
                </div>
                <livewire:book-import :file="request()->query('file', '')" />
                <div class="hidden sm:block text-sm">
                    <b>Avertissements et restrictions :</b>
                    <br />- Il est possible d'analyser ou d'importer des fichiers JSON, COL ou CSV ; un seul import à la fois.
                    <br />- Fichiers de {{ \App\Livewire\BookImportAnalysis::MAX_BOOKS }} livres au plus
                    ({{ \App\Livewire\BookImportAnalysis::MAX_FILE_KB }} Ko max) : pour un fichier plus gros, le découper en morceaux.
                    <br />- Simulation : l'import complet est exécuté puis annulé (détecte aussi les erreurs SQL).
                    <br />- Import réel : chaque livre est enregistré dans sa propre transaction ; en cas d'erreur, l'import s'arrête
                    et les livres déjà importés sont conservés.
                    <br />- Les publications créées portent l'administrateur connecté comme auteur de la saisie.
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
