{{-- Admin base layout --}}
@extends('layouts.app')

@section('content')
  <div class="max-w-6xl mx-auto p-6">
    @if(session('status'))
      <div class="p-2 mb-4 bg-green-100 text-green-800 rounded">
        {{ session('status') }}
      </div>
    @endif

    {{-- All admin pages render into this same "content" section --}}
    @yield('content')
  </div>
@endsection
