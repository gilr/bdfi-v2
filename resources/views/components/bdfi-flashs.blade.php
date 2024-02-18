@if (session('info'))
   <x-bdfi-alert type='info'>
    {{ session()->pull('info') }}
  </x-bdfi-alert>
@endif
@if (session('warning'))
   <x-bdfi-alert type='warning'>
    {{ session()->pull('warning') }}
  </x-bdfi-alert>
@endif
@if (session('danger'))
   <x-bdfi-alert type='danger'>
    {{ session()->pull('danger') }}
  </x-bdfi-alert>
@endif
