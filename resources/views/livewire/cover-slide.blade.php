<div>
    {{-- Liste des images, la première plus grande et les autres en miniature dessous --}}
    @foreach ($covers as $mycover)
        @if ($loop->first)
            <img style="max-width: 250px; max-height:450px" class='p-1 mx-auto hover-shadow border border-purple-800' src="{{ $mycover['url'] }}" wire:click="openCover(0)" alt="couverture" title="{{ $mycover['name'] }}">
            <div class="grid grid-cols-{{ $loop->count - 1 }} mx-auto max-w-fit">
        @else
            <div class="w-14 py-2 px-0.5"><img class="px-0.5 hover-shadow" src="{{ $mycover['url'] }}" wire:click="openCover({{ $loop->iteration - 1 }})" /></div>
        @endif
        @if ($loop->last)
            </div>
        @endif
    @endforeach

    {{-- Fenêtre modale de zoom d'une image, avec slide possible --}}
    @if($isOpen)
        <div class="coverslide-modal">
            <span class="coverslide-close" title="Refermer" wire:click="closeCover">&times;</span>
            <div class="coverslide-content">
                <div class="coverslide-img">
                    <span class="rounded ml-2 px-1 bg-violet-100"> {{ $currentIndex + 1}} / {{ count($covers) }} - {{ $covers[$currentIndex]['name'] }} </span>
                    <img
                        class="imag"
                        title="Refermer"
                        wire:click="closeCover"
                        src="{{ $covers[$currentIndex]['url'] }}"
                        alt="{{ $covers[$currentIndex]['name'] }}"
                    />
                </div>
                @if (count($covers) > 1)
                    <a class="coverslide-prev" title="Précédente" wire:click="prevCover">&#10094;</a>
                    <a class="coverslide-next" title="Suivante" wire:click="nextCover">&#10095;</a>
                @endif
            </div>
        </div>
    @endif

    <style>
    img.hover-shadow {
      transition: 0.3s;
    }

    .hover-shadow:hover {
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 12px 0 rgba(0, 0, 0, 0.19);
        cursor: pointer;
    }

    .coverslide-modal {
        opacity: 1;
        position: fixed;
        z-index: 99;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background: rgba(0, 0, 0, 0.8);
        padding: 10px;
        margin: 0;
    }

    .coverslide-content {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: center;
        width: 1000px;
        height: 100%;
        margin: auto;
        padding: 5px;
        opacity: 1;
    }

    .coverslide-img {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        margin: auto;
        max-height: 100%;
        max-width: 100%;
    }

    .coverslide-img span {
        z-index: 2;
        margin: auto;
    }
    .coverslide-img .imag {
        opacity: 1;
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        background-color: white;
        box-shadow: 0px 0px 5px 10px rgba(250, 250, 250, 0.8), 0px 0px 10px 15px rgba(50, 50, 250, 0.5);
        margin: auto;
        max-height: 100%;
        max-width: 100%;
        object-fit: contain;
        box-sizing: border-box;
    }

    .coverslide-close {
        z-index: 1;
        position: absolute;
        top: 10px;
        right: 25px;
        color: white;
        padding: 0px 12px 5px 12px;
        font-size: 35px;
        font-weight: 800;
        border-radius: 5px;
    }

    .coverslide-prev,
    .coverslide-next {
      z-index: 1;
      cursor: pointer;
      position: absolute;
      top: 50%;
      width: auto;
      margin-top: -50px;
      color: white;
      padding: 5px 16px;
      font-size: 35px;
      font-weight: bold;
      transition: 0.6s ease;
      border-radius: 4px;
      user-select: none;
      -webkit-user-select: none;
    }

    .coverslide-next {
      right: 0;
    }

    .coverslide-next:hover,
    .coverslide-prev:hover,
    .coverslide-img > .imag:hover,
    .coverslide-close:hover,
    .coverslide-close:focus {
        text-decoration: none;
        cursor: pointer;
        background-color: rgba(128, 0, 128, 1);
        color: violet;
    }
    </style>

</div>
