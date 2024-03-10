@foreach ($laureats as $laureat)
    <div class='ml-2 md:ml-8'>
        {{ $laureat["year"] }} -
        <a class='hover:bg-yellow-100 border-b hover:border-purple-400' href='/prix/{{ $laureat["award_name"] }}'>{{ $laureat["award_name"] }},</a>
        catégorie
        <a class='hover:bg-yellow-100 border-b hover:border-purple-400' href='/prix/categorie/{{ $laureat["categorie_id"] }}'>{{ $laureat["categorie_name"] }}</a>
        {!! $laureat["title"] != "" ? ": " . $laureat["title"] : "" !!} {{ $laureat["title"] == "" ? $laureat["vo_title"] : ($laureat["vo_title"] == "" ? "" : "(" . $laureat["vo_title"] . ")") }}
    </div>
@endforeach