<div>
    <div>
        <img style="max-width: 250px; max-height:450px" class='p-1 mx-auto hover-shadow border border-purple-800' src="{{ $image['url'] }}" wire:click="toggleCover" alt="couverture" title="{{ $image['name'] }}" />
    </div>

    @if($isOpen)
        <div class="coverzoom-modal">
            <span class="coverzoom-close" title="Refermer" wire:click="toggleCover">&times;</span>
            <img class="coverzoom-content" title="Refermer" wire:click="toggleCover" src="{{ $image['url'] }}" alt="{{ $image['name'] }}" />
     </div>
   @endif

<style>
    .coverzoom-modal {
        opacity: 1;
        position: fixed;
        z-index: 99;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background: rgba(0, 0, 0, 0.8);
        object-fit: contain;
        box-sizing: border-box;
        padding: 10px;
        margin: 0;
    }

    .coverzoom-content {
        position: relative;
        display: flex;
        justify-content: center;
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        box-sizing: border-box;
        margin: auto;
        background-color: white;
        box-shadow: 0px 0px 5px 10px rgba(250, 250, 250, 0.8), 0px 0px 10px 15px rgba(50, 50, 250, 0.5);
    }

    .hover-shadow:hover {
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 12px 0 rgba(0, 0, 0, 0.19);
        cursor: pointer;
    }

    .coverzoom-close {
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

    .coverzoom-content:hover,
    .coverzoom-close:hover,
    .coverzoom-close:focus {
        text-decoration: none;
        cursor: pointer;
        background-color: rgba(128, 0, 128, 1);
        color: violet;
    }

</style>

</div>
