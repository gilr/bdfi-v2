<?php include('commun/config.inc.php') ?>
<?php include('commun/feed.inc.php') ?>
<?php include('commun/outils_wiki.inc.php') ?>
<?php require('commun/lib_recherche.php') ?>
<?php require('commun/lib_bdfi.php') ?>
<?php require('commun/outils_sgbd.inc.php') ?>
<?php require('commun/appels_sgbd.inc.php') ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
 <title>BDFI / ImaginE : la Base de Donn&eacute;es Francophone de l'Imaginaire</title>
 <link rel="icon" type="image/x-icon" href="favicon32.ico" />
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
 <meta name="description" content="Page d'accueil de BDFI, Base de Donn&eacute;es Francophone de l'Imaginaire" /> 
 <meta name="keywords" content="Imaginaire, science-fiction, fantatsique, fantasy, horreur, bibliographies, auteurs, th&egrave;mes, genres, prix, r&eacute;compenses litt&eacute;raires, quizz, revues, romans, nouvelles" /> 
 <link rel="stylesheet" type="text/css" media="screen" title="Defaut" href="styles/bdfi.css" />
 <script type="text/javascript" src="../scripts/jquery-1.4.1.min.js"></script>
 <script type="text/javascript" src="../scripts/jquery-ui-1.7.2.custom.min.js"></script>
<!-- <script type='text/javascript' src='../scripts/neige.js'></script> -->
 <script type="text/javascript" src="../scripts/popup_v3.js"></script>
 <script type='text/javascript' src='../scripts/outils_v2.js'></script>
 <?php include('commun/image.inc.php') ?>
<style type="text/css">
.bloc ul {
 margin: 0;
 padding-left: 15px;
}
.bloc ul li {
 padding: 1px 0 1px 5px;
}
.bloc dl { 
 padding: 0;
 margin: 0;
}
.bloc dl dt { 
 color: #850;
 padding: 0;
 margin: 0;
 float: left;
 text-align: left;
}
.bloc dl dd { 
 padding: 0;
 margin: 0;
 text-align: right;
}

.colonne {
 float: right; 
 width: 33%; 
 margin: 0; 
 padding: 0; 
 border: 0;
 /* background: #dd8; */ 
}
.blocdbl
{
 float: right; 
 width: 66%; 
 margin: 0; 
 padding: 0; 
 /* background: #8dd; */
}

.bloc {
 margin: 5px; 
 padding: 0 5px; 
 border: 1.5px outset #fec;
 background: #fec;
 font-size: 0.9em;
 /* text-align: justify; */
}
.inbloc {
 background: #cfc;
 background: #eec2af;
 background: url(images/fond_journal.png) repeat;
 border: 1px solid #bbb;
 padding: 5px;
}
.fond2 {
 background: url(images/fond_journal2.png) repeat;
}
.fond3 {
 background: url(images/fond_journal3.png) repeat;
}

.bloc h2 {
 margin: 0 0 2px 0;
 text-align: center;
 padding: 0;
 color: black;
 padding-bottom: 3px;
 padding-top: 3px;
 font-family: Verdana, Helvetica, sans-serif; 
 font-size: 1.2em;
 background: #ccc url(images/fond_bouton.png) repeat-x;
 /* background: #ffe; */
 border: 0;
 border-bottom: 1px solid #ccc;
}

.pref {
 background:#fc7;
}
</style>
</head>

<body id='bdfi'>
<div id='conteneur'>
<?php include('commun/bandeau-v2.inc.php') ?>

<div id='menu'>
<?php include('commun/menu_accueil-v2.inc.php') ?>
<br />
<dl id='signet'>
<dd>
 <br />
 <div style='text-align:center'>
 <script type='text/javascript'>
  aff_signet("", 1, "");
 </script>
 </div>
<br />
</dd></dl>

</div>

<div id="page_menu">

<div class='colonne'>
<!--- Colonne de droite !-->
<div class="bloc" style='text-align:center;'>
<div class="inbloc fond2" style='text-align:center;'>
 <h2> <span style='color:#888;font-size:small;text-decoration:none;'>&larr;</span>
 Quelques parutions récentes
 <a style='color:#888;font-size:small;text-decoration:none;' href='https://www.bdfi.net/imagine/doku.php?id=base:sp:tourniquet'>&rarr;</a>
 </h2>

 <?php include('commun/tourniquet-v2.inc.php') ?>
 <div class="mini-slide">
<?php
  while ($sp = array_pop($table_sp)) {
    list($sp_c, $sp_m) = explode ("::", $sp);
    echo "  <a class='cover expandable' href='$bdfi_url_couvs$sp_c'>";
    echo "<img style='border:10px solid #ccc;' alt='SP r&eacute;cents' title='Cliquer pour agrandir' src='${bdfi_url_couvs_small}$sp_m' />";
    echo "</a>\n";
  }
?>
  <form class='bdfi' action="get" style='text-align:center;'><div>
    <input type='button' value=' Stop ' />
    <input type='button' value=' Go / Next ' />
  </div></form>
 </div>
</div>
</div>

<div class="bloc"><div class="inbloc">
<h2><span style='color:#888;font-size:small;text-decoration:none;'>&larr;</span> Auteurs décédés ce jour de l'année <span style='color:#888;font-size:small;text-decoration:none;'>&rarr;</span></h2>
  <?php resultat_anniv (1, 1, ""); ?>
</div></div>

<div class="bloc"><div class="inbloc fond3">
<?php lire_bloc ("bloc_liens_internes");?>
</div></div>

<div class="bloc"><div class="inbloc">
<h2><span style='color:#888;font-size:small;text-decoration:none;'>&larr;</span>Wiki - Imagine<span style='color:#888;font-size:small;text-decoration:none;'>&rarr;</span></h2>
Derni&egrave;res modifications :<br /><br />
<!-- &nbsp;&nbsp;&nbsp;En panne ! -->
<?php wiki(); ?>
 <br /><br />&gt;&gt;&gt;  Voir le <a href= "https://www.bdfi.net/imagine/feed.php?num=25&type=rss2&linkto=current">flux RSS du wiki</a>
</div></div>

<div class="bloc"><div class="inbloc fond3">
<h2><span style='color:#888;font-size:small;text-decoration:none;'>&larr;</span> La base <span style='color:#888;font-size:small;text-decoration:none;'>&rarr;</span></h2>
Derni&egrave;res mises &agrave; jour des recueils :<p>
<?php lastfiles (1, 'Recueils', 'recueils/pages', 8, "data/cache_Recueils", 0); ?>
</p> 
&gt;&gt;&gt; Page des <a href="site/maj_recentes.php?b=Recueils">mises &agrave; jour</a><br />
&gt;&gt;&gt; Index des <a href="recueils/">recueils</a>
</div></div>

</div>

<div class="blocdbl">
<!--- Bloc sur les deux colonnes de gauche !-->
<div class="bloc pref"><div class="inbloc fond3" style="text-align:justify">
<?php lire_bloc ("bloc_presentation");?>
</div></div>
</div>

<div class='colonne'>
<!--- Colonne du centre !-->
<div class="bloc"><div class="inbloc fond3">
<h2><span style='color:#888;font-size:small;text-decoration:none;'>&larr;</span> Le forum <span style='color:#888;font-size:small;text-decoration:none;'>&rarr;</span></h2>
Derni&egrave;res discussions&nbsp;:
<ul>
  <?php $_GET['action'] = 'feed'; ?>
  <?php $_GET['fid'] = '3,5,12,11,32,19,31,10,8,18,21,16,17'; ?>
  <?php $_GET['show'] = '11'; ?>
  <?php $_GET['type'] = 'html'; ?>
  <?php include('./punbb_extern.php') ?>
</ul>
<br />&gt;&gt;&gt; Acc&egrave;s aux <a href="https://forums.bdfi.net/">Forums</a>
</div></div>

<div class="bloc"><div class="inbloc">
<h2><span style="color:#888;font-size:small;text-decoration:none;">&larr;</span> Annonces de l'imaginaire <a style="color:#888;font-size:small;text-decoration:none;" href="adminbdfi3/public/admin/evenements">&rarr;</a></h2>
<br>
Les prochains évènements :
<br>
<?php display_events_summary(); ?>
<br />&gt;&gt;&gt; Voir tous les <a href="/site/evenements">évènements</a>
</div></div>

<div class="bloc"><div class="inbloc fond2">
<h2><span style='color:#888;font-size:small;text-decoration:none;'>&larr;</span> La base <span style='color:#888;font-size:small;text-decoration:none;'>&rarr;</span></h2>

Derni&egrave;res mises &agrave; jour auteurs :<p>
<?php lastfiles (3, 'Auteurs', 'auteurs', 10, "data/cache_Auteurs", 0); ?>
</p> 
&gt;&gt;&gt; Page des <a href="site/maj_recentes.php">mises &agrave; jour</a><br />
&gt;&gt;&gt; Index des <a href="auteurs/">auteurs</a>
</div></div>

<div class="bloc"><div class="inbloc fond2">
<h2><span style='color:#888;font-size:small;text-decoration:none;'>&larr;</span> La base <span style='color:#888;font-size:small;text-decoration:none;'>&rarr;</span></h2>
Derni&egrave;res mises &agrave; jour des prix :<p>
<?php lastfiles (3, 'Prix', 'prix/pages', 5, "data/cache_Prix", 0); ?>
</p> 
&gt;&gt;&gt; Page des <a href="site/maj_recentes.php?b=Series">mises &agrave; jour</a><br />
&gt;&gt;&gt; Index des <a href="prix/">prix</a>
</div></div>

<div class="bloc"><div class="inbloc fond3">
<h2><span style="color:#888;font-size:small;text-decoration:none;">&larr;</span> Etat de la base  <a style="color:#888;font-size:small;text-decoration:none;" href="http://www.bdfi.net/adminbdfi3/public/admin/stats">&rarr;</a></h2>
<br>
<?php
/* lire_bloc ("bloc_etat_base"); */
$record = get_stats_summary();

if ($record) {
  echo "<dl>";
  echo "<dt>Auteurs</dt><dd>" . $record['auteurs'] . "</dd>\n";
  echo "<dt>Séries et cycles</dt><dd>" . $record['series'] . "</dd></dl><br />\n";
  echo "<dl>";
  echo "<dt>Références</dt><dd>" . $record['references'] . "</dd></dl>\n";
  echo "&nbsp; dont";
  echo "<dl>";
  echo "<dt>Romans et fix-up</dt><dd>" . $record['romans'] . "</dd>\n";
  echo "<dt>Nouvelles</dt><dd>" . $record['nouvelles'] . "</dd>\n";
  echo "<dt>Recueils et anthologies</dt><dd>" . $record['recueils'] . "</dd>\n";
  echo "<dt>Revues et fanzines</dt><dd>" . $record['revues'] . "</dd>\n";
  echo "<dt>Guides, essais...</dt><dd>" . $record['essais'] . "</dd>\n";
  echo "</dl>";
} ?>
</div></div>

</div>

<div class='colonne'>
<!--- Colonne de gauche !-->

<div class="bloc"><div class="inbloc">
<h2><span style='color:#888;font-size:small;text-decoration:none;'>&larr;</span> Programmes et parutions <span style='color:#888;font-size:small;text-decoration:none;'>&rarr;</span></h2>
Derni&egrave;res informations :
<ul>
  <?php $_GET['action'] = 'feed'; ?>
  <?php $_GET['fid'] = '14,15,28'; ?>
  <?php $_GET['show'] = '15'; ?>
  <?php $_GET['type'] = 'html'; ?>
  <?php include('./punbb_extern.php') ?>
</ul>
<br />&gt;&gt;&gt; Les <a href="https://forums.bdfi.net/viewforum.php?id=14">programmes</a> sur le forum.
</div></div>

<div class="bloc"><div class="inbloc fond2">
<?php 
  $today = date("d/m");
  echo "<h2><span style='color:#888;font-size:small;text-decoration:none;'>&larr;</span> Anniversaires de naissance ($today) <span style='color:#888;font-size:small;text-decoration:none;'>&rarr;</span></h2>";
  resultat_anniv (0, 1, ""); ?>
</div></div>

<div class="bloc"><div class="inbloc fond3">
<?php lire_bloc ("bloc_divers_web");?>
</div></div>

<div class="bloc"><div class="inbloc">
<h2><span style='color:#888;font-size:small;text-decoration:none;'>&larr;</span> La base <span style='color:#888;font-size:small;text-decoration:none;'>&rarr;</span></h2>

Derni&egrave;res mises &agrave; jour cycles et s&eacute;ries :<p>
<?php lastfiles (1, 'Series', 'series/pages', 8, "data/cache_Series", 0); ?>
</p> 
&gt;&gt;&gt; Page des <a href="site/maj_recentes.php?b=Series">mises &agrave; jour</a><br />
&gt;&gt;&gt; Index des <a href="series/">cycles et s&eacute;ries</a>
</div></div>

</div>

<div class="warning"  style="clear:both">ATTENTION : nous n'utilisons jamais les adresses en <strong>bdfi.net</strong> pour <b>envoyer</b> des messages (seulement en r&eacute;ception). Tous les courriers semblant &eacute;maner de ces adresses sont des spams et virus qui "d&eacute;guisent" leur adresse source. Ils peuvent &ecirc;tre d&eacute;truits (m&eacute;fiez-vous des fichiers joints).</div>

<div style="text-align:justify">Malgr&eacute; notre attention, le contenu de certain mails peut &ecirc;tre oubli&eacute; ou pris en compte de fa&ccedil;on incompl&egrave;te. Ce ne sera jamais volontaire. Ne vous vexez pas, mais envoyez nous un petit mail de rappel, si apr&egrave;s un mois, vous ne voyez rien venir !
<br />Le contr&ocirc;le des pages et donn&eacute;es restant une lourde t&acirc;che, n'h&eacute;sitez pas &agrave; nous signaler toute coquille ou erreur, aussi minime soit-elle.
&nbsp;&nbsp;&nbsp;</div>

</div>
<?php include('commun/pied.inc.php'); ?>
</div>
</body>
</html>
