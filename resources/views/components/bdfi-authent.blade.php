    @if (Route::has('login'))
            @auth
                @if (Auth::user()->role != 'user')
                    <a href="{{ url('/admin') }}" class="text-sm text-gray-700 underline"> {{ __('Administration') }} </a> - 
                    <a href="{{ url('/filament') }}" class="text-sm text-gray-700 underline"> {{ __('Gestion des tables') }} </a> -
                @endif
                <form method="POST" style="display:inline" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-sm text-gray-700 underline"> {{ __('Log out') }} </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="text-sm text-gray-700 underline"> {{ __('Log in') }} </a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="ml-4 text-sm text-gray-700 underline"> {{ __('Register') }} </a>
                @endif
            @endauth
    @endif
