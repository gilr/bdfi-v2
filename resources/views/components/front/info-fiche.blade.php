@auth
    @if (auth()->user()->hasGuestRole())
        <div class='text-sm p-2 my-2 rounded-sm lg:mx-40 self-center border border-blue-400 w-11/12 bg-sky-100 text-blue-900 shadow-md shadow-sm shadow-blue-600'>

@php
$authent = Auth::user()->name;
$role = Auth::user()->role->getLabel();
$vrole = Auth::user()->role->value;
$creator = $results->creator->name;
$editor = $results->editor->name;

$blockTitle = <<< END
Bloc d'information fiche.
END;

$blockIntro = <<< END
Membre authentifié sous le nom <b>$authent</b>, $role BDFI (role <i>'$vrole'</i>)
END;

@endphp

            <livewire:collapsible-block
                title="{!! $blockTitle !!}"
                intro="{!! $blockIntro !!}"
                content="{!! $content !!}"
            />
        </div>
    @endif
@endauth
