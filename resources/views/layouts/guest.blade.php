<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <meta http-equiv="X-UA-Compatible" content="ie=edge" />
  <title>{{ \App\Models\AppSetting::get('app_name', config('app.name')) }}</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">

  @vite(['resources/css/app.css'])
</head>

<body class="antialiased d-flex flex-column" style="background-color: var(--apple-bg); min-height: 100vh;">
  <div class="page page-center" style="position: relative; overflow: hidden;">
    {{-- Future Background Decoration using inline styles for reliability --}}
    <div style="position: absolute; top: -10%; left: -10%; width: 40%; height: 40%; background: rgba(224, 90, 38, 0.05); filter: blur(100px); border-radius: 50%;"></div>
    <div style="position: absolute; bottom: -10%; right: -10%; width: 40%; height: 40%; background: rgba(0, 84, 166, 0.05); filter: blur(100px); border-radius: 50%;"></div>

    <div class="container container-tight py-4" style="position: relative; z-index: 10;">
      <div class="text-center mb-4">
        <a href="." class="navbar-brand navbar-brand-autodark">
          @if(\App\Models\AppSetting::get('app_logo'))
            <img src="{{ asset('storage/' . \App\Models\AppSetting::get('app_logo')) }}" alt="Logo"
              class="navbar-brand-image" style="height: 60px; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));">
          @else
            <h1 class="text-brand-gradient" style="font-size: 2.5rem; font-weight: 800;">{{ \App\Models\AppSetting::get('app_name', config('app.name')) }}</h1>
          @endif
        </a>
      </div>

      @yield('content')

      <div class="text-center text-muted mt-4">
        &copy; {{ date('Y') }} <a href="https://pakeberes.id" class="text-muted">Pakeberes</a>. All rights reserved.
      </div>
    </div>
  </div>
</body>

</html>