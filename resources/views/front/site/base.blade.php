@extends('front.layout')
@section('content')

<link rel="stylesheet" href="/css/morris.css">
<script type="text/javascript" src="/js/jquery-1.8.0.min.js"></script>
<script type='text/javascript' src="/js/morris.min.js"></script>
<script type='text/javascript' src="/js/raphael-min.js"></script>

<x-bdfi-menu-site tab='base' />

<div class='text-2xl my-2 md:mt-8 bold self-center'>
    <img sb-icon src="/img/{{ $icon }}" class="w-4 md:w-8 inline" title="{{ $title }}"/> <b>Evolution de la base BDFI - Historique & stats des référencements</b>
</div>

<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
    @if ($record = $results->last())
        <div class='text-base font-bold text-purple-900'>
            La dernière image statistique ({{ StrDateformat($record->date->format('Y-m-d')) }})
        </div>
        <div class='text-base ml-2 sm:ml-8'>
            <div>Auteurs : {{ $record->authors }}</div>
            <div>Séries et cycles : {{ $record->series }}</div>
            <div>Références  : {{ $record->references }}</div>
            <div>&nbsp; dont</div>
            <div>Romans et fix-up : {{ $record->novels }}</div>
            <div>Nouvelles : {{ $record->short_stories }}</div>
            <div>Recueils et anthologies : {{ $record->collections }}</div>
            <div>Revues et fanzines : {{ $record->magazines }}</div>
            <div>Guides, essais... : {{ $record->essays }}</div>
        </div>

        <div class='text-base py-2 font-bold text-purple-900'>
            Nombre de références de textes, dont romans et nouvelles
        </div>
        <div class='text-base p-1 grid justify-items-center'>
            <div id="references" class="border-2 border-purple-500 bg-gray-100 rounded w-10/12 h-96"></div>
        </div>
        <div class='text-base py-2 font-bold text-purple-900'>
            Nombre d'auteurs et de séries
        </div>
        <div class='text-base p-1 grid justify-items-center'>
            <div id="auteurs" class="border-2 border-purple-500 bg-gray-100 rounded w-10/12 h-96"></div>
        </div>
        <div class='text-base py-2 font-bold text-purple-900'>
            Nombre de recueils, de revues & fanzines et de guides & essais
        </div>
        <div class='text-base p-1 grid justify-items-center'>
            <div id="autres" class="border-2 border-purple-500 bg-gray-100 rounded w-10/12 h-96"></div>
        </div>

    @else
        <div class='text-base p-20 md:px-10 mt-1 mx:4 md:mx-20 mb-2 border-b border-red-500'>
            Base de donnée inaccessible.
        </div>
    @endif

</div>

<script>
Morris.Line({
  element: 'auteurs',
  data: [
<?php
$un = 0;
if ($results) {
    foreach ($results as $record) {
        $date = $record['date'];
        echo "{ date: '$date'";
        echo ", a: " . $record['authors'];
        echo ($record['series'] == NULL ? "" : ", s: " . $record['series']);
        echo " },\n";
    }
}
?>
  ],
  xkey: 'date',
  ykeys: ['a', 's'],
  ymax: '16000',
  labels: ['Auteurs', 'Cycles & Séries'],
  lineColors: ['#88f', '#f88'],
  xLabels: "year",
  xLabelAngle: '45',
  events: ['2002', '2003', '2004', '2005', '2006', '2007', '2008', '2009', '2010', '2011', '2012', '2013', '2014', '2015', '2016', '2017', '2018', '2019', '2020', '2021'],
  eventLineColors: ['#ec9', '#ec9', '#ec9', '#ea5', '#ec9']
});
</script>

<script>
Morris.Line({
  element: 'references',
  data: [
<?php
$un = 0;
if ($results) {
    foreach ($results as $record) {
        $date = $record['date'];
        echo "{ date: '$date'";
        echo ", t: " . $record['references'];
        echo ", r: " . $record['novels'];
        echo ", n: " . $record['short_stories'];
        echo " },\n";
    }
}
?>
  ],
  xkey: 'date',
  ykeys: ['t', 'n', 'r'],
  ymax: '160000',
  labels: ['Références', 'Nouvelles', 'Romans'],
  lineColors: ['#88d', '#a88', '#484'],
  xLabels: "year",
  xLabelAngle: "45",
  events: ['2002', '2003', '2004', '2005', '2006', '2007', '2008', '2009', '2010', '2011', '2012', '2013', '2014', '2015', '2016', '2017', '2018', '2019', '2020', '2021'],
  eventLineColors: ['#ec9', '#ec9', '#ec9', '#ea5', '#ec9']
});
</script>

<script>
Morris.Area({
  element: 'autres',
  data: [
<?php
$un = 0;
if ($results) {
    foreach ($results as $record) {
        $date = $record['date'];
        echo "{ date: '$date'";
        echo ($record['collections'] == NULL ? "" : ", r: " . $record['collections']);
        echo ($record['magazines'] == NULL ? "" : ", m: " . $record['magazines']);
        echo ($record['essays'] == NULL ? "" : ", g: " . $record['essays']);
        echo " },\n";
    }
}
?>
  ],
  xkey: 'date',
  ykeys: ['r', 'm', 'g'],
  ymax: '16000',
  labels: ['Recueils, anthos', 'revues, fanzines', 'Essais, guides'],
  lineColors: ['#6b6', '#48d', '#d84'],
  xLabels: "year",
  xLabelAngle: '45',
  events: ['2002', '2003', '2004', '2005', '2006', '2007', '2008', '2009', '2010', '2011', '2012', '2013', '2014', '2015', '2016', '2017', '2018', '2019', '2020', '2021'],
  eventLineColors: ['#ec9', '#ec9', '#ec9', '#ea5', '#ec9']
});
</script>

@endsection