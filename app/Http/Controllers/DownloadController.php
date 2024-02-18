<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class DownloadController extends Controller
{
    public function index()
    {
        return view('admin.telechargements.index');
    }

    public function exportCSV(Request $request, $model)
    {
        // Contrôle existence du modèle
        if (!class_exists("App\\Models\\$model"))
        {
            $request->session()->flash('flash.banner', 'Le modèle demandé ('.$model.') pour le téléchargement n\'existe pas.');
            $request->session()->flash('flash.bannerStyle', 'danger');
            return redirect('/admin/telechargements');
        }

        // Nom du fichier tel qu'il sera téléchargé
        $filename = "${model}_" . date ("Y-m-d") . '.csv';
        $headers = [
            'Content-type' => 'application/csv',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename,
            'Expires' =>'0',
            'Pragma' =>'public'
        ];

        $collection = call_user_func(array("App\\Models\\$model", 'all'));
        $table = $collection->toArray();

        # Ajout des noms de colonne en première ligne
        array_unshift($table, array_keys($table[0]));

        # Balayage de la table pour écrire dans le stream
        $callback = function() use ($table) {
            $handle = fopen('php://output', 'w');
            foreach ($table as $row) {
                fputcsv($handle, str_replace(array("\r\n", "\n", "\r"), ' ', $row), ";", '"');
            }
            fclose($handle);
        };
        return Response()->stream($callback, 200, $headers);
    }

}
