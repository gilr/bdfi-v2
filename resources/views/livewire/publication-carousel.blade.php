<div class="carousel-container">
    @if (count($images) > 0)
        <div id="carousel-image" class="carousel-image">
            <img class='px-1 mx-auto border shadow-[0_35px_60px_-15px_rgba(0,0,0,0.3)]' src="{{ $images[$currentIndex]['url'] }}" alt="{{ $images[$currentIndex]['caption'] }}">
            <div class="carousel-caption">
                <h5>{{ $images[$currentIndex]['caption'] }}</h5>
            </div>
        </div>

        <div class="carousel-controls">
            <button wire:click="prevImage" class='text-sm border bg-purple-100 px-1.5 border-purple-700 rounded shadow-md shadow-indigo-500/40 hover:text-purple-700 focus:text-purple-900'>&#10094;</button>
            <button wire:click="nextImage" class='text-sm border bg-purple-100 px-1.5 border-purple-700 rounded shadow-md shadow-indigo-500/40 hover:text-purple-700 focus:text-purple-900'>&#10095;</button>
        </div>

        <div class="carousel-indicators">
            @foreach ($images as $index => $image)
                <span wire:click="setImage({{ $index }})" class="indicator {{ $currentIndex == $index ? 'active' : '' }}"></span>
            @endforeach
        </div>
    @else
        <p>No images available.</p>
    @endif

    <style>
        .carousel-container {
            /* position: relative;
            width: 100%;
            max-width: 600px;
            margin: auto; */
            text-align: center;
        }
        .carousel-image img {
            height: 350px;
            /* height: auto;
             border-radius: 10px; */
        }
        .carousel-controls {
            display: flex;
            justify-content: space-between;
            margin-top: -25px;
        }
        .carousel-controls button {
            padding: 5px 10px;
            /*border: none;
            background-color: #333;
            color: #fff;
            cursor: pointer;
            border-radius: 5px; */
        }
        .carousel-controls button:hover {
            background-color: #555;
        }
        .carousel-indicators {
            text-align: center;
            padding: 10px 25px 0 25px;
        }

        .indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            margin: 0 2px;
            background-color: #ccc;
            border-radius: 50%;
            cursor: pointer;
        }

        .indicator.active {
            background-color: rgb(126 34 206);
        }

    </style>

</div>

@script
<script>
    setInterval(() => {
       $wire.nextImage();
    }, 5000);
</script>
@endscript