<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>{{ config('app.name', 'Kleening') }}</title>
    @vite(['resources/css/app.css'])
  </head>
  <body class="d-flex flex-column">
    <div class="page page-center">
      <div class="container container-tight py-4">
        <div class="text-center mb-4">
          <a href="." class="navbar-brand navbar-brand-autodark">
            {{-- You can replace this with your application logo component --}}
            <img src="{{ asset('storage/logo_kleening.png') }}" alt="Kleening Logo" class="navbar-brand-image" style="height: 100px;">
          </a>
        </div>
        
        @yield('content')

      </div>
    </div>
  </body>
</html>