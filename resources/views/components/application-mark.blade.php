@if (Auth::user()->role->value != App\Enums\UserRole::USER->value)
    <div class="mx-auto p-2 text-2xl text-yellow-600 bold bg-yellow-100 border border-2 border-yellow-400 rounded-full shadow-lg opacity-90">
        BDFI<span class="text-xs text-black">admin</span>
    </div>
@else
    <div class="mx-auto p-2 text-2xl text-blue-600 bold bg-blue-100 border border-2 border-blue-400 rounded-full shadow-lg opacity-90">
        BDFI<span class="text-xs text-black">user</span>
    </div>
@endif
