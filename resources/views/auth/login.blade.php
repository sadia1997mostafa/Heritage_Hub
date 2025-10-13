@extends('layouts.app')

@section('title','Login')

@section('content')
  {{-- Render the auth modal partial centered like a popup. The modal partial contains both login & register panes. --}}
  @php
    // allow a redirect query to be passed through to the modal form
    $redirectTo = request('redirect') ?? ($redirect ?? '');
  @endphp

  <div class="hh-container pad-section" aria-hidden="true">
    {{-- keep an offscreen heading for accessibility; actual modal is shown below --}}
    <h2 class="visually-hidden">Login</h2>
  </div>

  {{-- Include the modal partial. It uses $redirect variable if present to prefill hidden input. --}}
  @include('partials.auth-modal-fixed', ['redirect' => $redirectTo])

  {{-- Auto-open modal on page load when this route is visited directly --}}
  <script>
    (function(){
      // open login tab explicitly
      window.addEventListener('DOMContentLoaded', function(){
        if (typeof window.__openAuth === 'function') {
          try { window.__openAuth('login'); } catch(e){}
        }
      });
    })();
  </script>

  <style>
    /* small helper to visually hide the page heading but keep accessible */
    .visually-hidden{position:absolute!important;height:1px;width:1px;overflow:hidden;clip:rect(1px,1px,1px,1px);white-space:nowrap}
  </style>

@endsection
