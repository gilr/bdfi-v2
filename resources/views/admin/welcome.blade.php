<x-app-layout>
    <!-- Page d'accueil administration (jetstream) -->
    <x-slot name="header">
        @if (Auth::user()->role->value != App\Enums\UserRole::USER->value)
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Administration BDFI - Rapports et outils') }}
            </h2>
        @else
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Utilisateur BDFI') }}
            </h2>
        @endif
    </x-slot>

    @if (Auth::user()->role->value != App\Enums\UserRole::USER->value)
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <x-admin.welcome />
                </div>
            </div>
        </div>
    @endif
</x-app-layout>
