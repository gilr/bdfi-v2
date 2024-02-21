@if (session('info'))
   <x-front.alert type='info'>
    {{ session()->pull('info') }}
  </x-front.alert>
@endif
@if (session('warning'))
   <x-front.alert type='warning'>
    {{ session()->pull('warning') }}
  </x-front.alert>
@endif
@if (session('danger'))
   <x-front.alert type='danger'>
    {{ session()->pull('danger') }}
  </x-front.alert>
@endif
