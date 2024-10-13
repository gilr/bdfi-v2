<div class="carousel-container">
    @if (count($images) > 0)
        <div class="carousel-image">
            <img class='px-1 mx-auto border shadow-[0_35px_60px_-15px_rgba(0,0,0,0.3)]' src="{{ $images[$currentIndex]['url'] }}" alt="{{ $images[$currentIndex]['caption'] }}">
            <div class="carousel-caption">
                <h5>{{ $images[$currentIndex]['caption'] }}</h5>
            </div>
        </div>

        <div class="carousel-controls">
            <button wire:click="prev" class='text-sm border bg-purple-100 px-1.5 border-purple-700 rounded shadow-md shadow-indigo-500/40 hover:text-purple-700 focus:text-purple-900'>&#10094;</button>
            <button wire:click="next" class='text-sm border bg-purple-100 px-1.5 border-purple-700 rounded shadow-md shadow-indigo-500/40 hover:text-purple-700 focus:text-purple-900'>&#10095;</button>
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
            max-width: 220px;
            max-height: 400px;
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
    </style>
</div>
