<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <meta http-equiv="X-UA-Compatible" content="ie=edge" />
  <title>{{ \App\Models\AppSetting::get('app_name', config('app.name')) }}</title>
  @vite(['resources/css/app.css'])
</head>

<body class="d-flex flex-column">
  <div class="page page-center">
    <div class="container container-tight py-4">
      <div class="text-center mb-4">
        <a href="." class="navbar-brand navbar-brand-autodark">
          {{-- You can replace this with your application logo component --}}
          {{-- You can replace this with your application logo component --}}
          @if(\App\Models\AppSetting::get('app_logo'))
            <img src="{{ asset('storage/' . \App\Models\AppSetting::get('app_logo')) }}" alt="Logo"
              class="navbar-brand-image" style="height: 100px;">
          @else
            <h1>{{ \App\Models\AppSetting::get('app_name', config('app.name')) }}</h1>
          @endif
        </a>
      </div>

      @yield('content')

    </div>
  </div>
</body>

</html>