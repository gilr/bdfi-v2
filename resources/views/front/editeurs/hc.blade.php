@extends('front.layout')

@section('content')

    <div class='text-2xl mt-8 self-center'>
        <b>{{ $publisher->name }} : publications hors collection ou regroupement</b>
    </div>

<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>

    <div class='text-base'>
        Editeur  : <span class='font-semibold'><a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/editeurs/{{ $publisher->slug }}'>{{ $publisher->name }}</a></span>
    </div>
    <div class='text-base'>
        <span class='font-semibold'>{{ count($results) }}</span> publications référencées.
    </div>

</div>

@if (count($results) < 21)

<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
    @if(count($results))
        <hr class="mx-24 my-2 border-dotted border-purple-800"/>

        <div class='text-base'>
            <span class='font-semibold'>Liste des publications :</span>
            @include ('front.editeurs._hc-publications')
        </div>
        <hr class="mx-8 my-4 border-red-300"/>
        <div class='text-base pt-4'>
            <span class='font-semibold'>Galerie :</span>
            @include ('front.editeurs._hc-gallery')
        </div>
    @endif
</div>


@else

    <style>
    input.rad { display: none; }
    input.rad + label { display: inline-block; }

    input.rad ~ .tab {
        display: none;
        overflow: hidden;
        border-top: 1px solid blue;
        padding: 12px;
    }

    input#tab1, input#tab2, input#tab3 { display: none; }

    #tab1:checked ~ .tab.content1,
    #tab2:checked ~ .tab.content2 { display: block; }
    #tab3:checked ~ .tab.content3 { display: block; }

    input.rad + label {
      border: 1px solid #999;
      background: #EEE;
      padding: 4px 12px;
      border-radius: 4px 4px 0 0;
      cursor: pointer;
      position: relative;
      top: 1px;
      width:240px;
    }
    input.rad:checked + label {
      background-color: rgb(233 213 255);
      border-bottom: 1px solid transparent;
      font-weight: 600;
    }

    .tab:after {
      content: "";
      display: table;
      clear: both;
    }

    .article { padding: 0 20px; margin:0; }
    .article p { display: block; margin:0 10px; padding:0 10px; }
    .article img { display: inline; }
    .article ul { margin:0 10px; padding:0 10px; list-style-position:outside; list-style-type: disc; }
    .article ol { margin:0 10px; padding:0 10px; list-style-position:outside; list-style-type: Upper-greek ; }


    </style>

    <hr class="mx-24 my-2 border-dotted border-purple-800 pt-2 display:block"/>

    <div class='block pt-2 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4'>
        <input class='rad' type="radio" name="tabs" checked="checked" id="tab1" />
        <label for="tab1">Liste des publications</label>
        <input class='rad' type="radio" name="tabs" id="tab2" />
        <label for="tab2">Galerie</label>

        <div class="tab content1 text-base">
            @include ('front.editeurs._hc-publications')
        </div>

        <div class="tab content2 text-base">
            @include ('front.editeurs._hc-gallery')
        </div>

    </div>
@endif

@endsection