@extends('admin.layout')

@section('main')

@include('admin.partials.entete', ['title' => trans('back/reports.missing-records'), 'icone' => 'list-alt'])

<div class="row">
    <div class="col-sm-12 bg-warning" style="border-radius:3px; border: 1px solid #f0ad4e">
        <h2>Il manque des fiches biographiques en base pour <span class="text-danger">{{ $nombre }}</span> auteurs</h2>
        <div class="col-lg-4 col-md-4" style="border-radius:3px; border:3px dotted blue">
            <b>Attention</b>, il peut s'agir non pas d'une absence, mais :
            <ul>
                <li>d'une mauvaise référence dans le champ 'Nom BDFI' en base</li>
                <li>d'une mauvaise écriture d'une signature dans mes fichiers</li>
            </ul>
        </div>
        <div class="col-lg-8 col-md-8" style="border-radius:3px; border:3px dotted crimson">
            <b>Avertissement :</b> depuis la version 0.31, pour plus d'efficacité, le clic sur "Créer la fiche" effectue une <b>création immédiate</b> de cette fiche, puis <b>redirige automatiquement sur son formulaire de modification</b> ce qui permet de la compléter - ou pas, en annulant, en revenant au menu via les liens, ou en refermant le navigateur ou l'onglet courant; Mais la fiche reste créée.<ul></ul>
        </div>
        <div class="col-lg-12 col-md-12" style="clear:all"> </div>
            <b>Nota :</b> il est possible par exemple de cliquer avec le bouton droit de la souris et de faire "ouvrir dans un nouvel onglet" sur les liens BNF, Google, ISFDB puis de  cliquer avec le bouton droit sur le bouton orange de création et de faire "Ouvrir dans une nouvelle fenêtre".<br />
        <br />
        <div><span class="label label-primary">Tip <i class="fa fa-lightbulb-o"></i></span> S'il ne manque pas beaucoup de fiches, vous pouvez également allez voir les <a href="{{url('/admin/reports/missing-countries')}}">Nationalités manquantes</a> !</div>

        <h2>La liste des fiches manquantes</h2>

        <ol>
            @foreach ($missing as $auteur)
            <li>
                Page BDFI de <a href='{!! url_auteur("$auteur") !!}' class="text-primary" role="button">{!! $auteur !!}</a>     &rarr; 
                <?php 
                    $a="";
                    $b="";
                    $c="";
                    $parts = explode (" ", $auteur, 3);
                    $a = isset($parts[0]) ? $parts[0] : "";
                    $b = isset($parts[1]) ? $parts[1] : "";
                    $c = isset($parts[2]) ? $parts[2] : "";
                    if ($c == "") {
                        $a = ucfirst (strtolower($a));
                        if (substr($a, 0, 2) == "Mc") {
                            $a = substr_replace($a, strtoupper(substr($a, 2, 1)), 2, 1);
                        }
                        $bnf = "$a, $b";
                        $google = "\"$b $a\"";
                        $isfdb = "$b+$a";
                        $prenom = $b;
                        $nom = $a;
                    }
                    else {
                        $b2 = substr($b, 1, 1);
                        if (($b2 >= "A") && ($b2 <= "Z")) {
                            $a = ucfirst (strtolower($a));
                            $b = ucfirst (strtolower($b));
                            $prenom = $c;
                            $nom = "$a $b";
                        }
                        else {
                            $a = ucfirst (strtolower($a));
                            $prenom = "$b $c";
                            $nom = $a;
                        }
                        $bnf = "$nom, $prenom";
                        $google = "\"$prenom $nom\"";
                        $isfdb = "$prenom+$nom";
                    }
                ?>
                <a href="{{ action('AuteurController@storeandmodify', ['nom_bdfi' => $auteur, 'nom' => $nom, 'prenom' => $prenom, 'sexe' => '\'?\'', 'date_naiss' => '0000-00-00', 'date_deces' => '0000-00-00', 'pseudo' => '0', 'pays_id' => '1', 'avancement_id' => '1']) }}" class="btn btn-warning btn-xs" title="Création et redirection sur la fiche pour la compléter.">Créer la fiche</a>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Recherche sur : [

                <?php 
                    $str = "http://www.isfdb.org/cgi-bin/adv_search_results.cgi?USE_1=author_canonical&OPERATOR_1=contains&TERM_1=" . $a .
                    "&CONJUNCTION_1=AND&USE_2=author_canonical&OPERATOR_2=contains&TERM_2=" . $b . 
                    "&CONJUNCTION_2=AND&USE_3=author_canonical&OPERATOR_3=exact&TERM_3=" . $c . "&ORDERBY=author_canonical&START=0&TYPE=Author";

                    echo "<a href='http://catalogue.bnf.fr/resultats-auteur.do?nomAuteur=" . $bnf . "&filtre=1&pageRech=rau' role='button'> BNF </a> ] &nbsp; [";
                    echo "<a href='http://www.isfdb.org/cgi-bin/se.cgi?arg=" . $isfdb . "&type=Name' class='text-primary' role='button'> ISFDB </a> ] &nbsp; [";
                    echo "<a href='" . $str . "' class='text-primary' role='button'> ISFDB avancée </a> ] &nbsp; [ ";
                    echo "<a href='https://www.google.fr/search?q=" . $google . "' class='text-primary' role='button'> Google </a>";
                ?>
                ]
            </li>
            @endforeach
        </ol>
    </div>
</div>

@endsection


