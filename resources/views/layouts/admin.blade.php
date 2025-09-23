<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Kleening.id') }} - @yield('title')</title>
    @vite(['resources/css/app.css'])
    @auth
    <script>
        // Ambil token dari session PHP dan simpan ke localStorage JavaScript
        const authToken = "{{ session('api_token') }}";
        if (authToken) {
            localStorage.setItem('auth_token', authToken);
        }
    </script>
    @endauth
  </head>
  <body>
    <div class="page">
      <aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
        <div class="container-fluid">
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <h1 class="navbar-brand navbar-brand-autodark">
            <a href="">
              {{-- Ganti dengan logo Anda nanti --}}
              Kleening.id
            </a>
          </h1>
          <div class="collapse navbar-collapse" id="sidebar-menu">
            <ul class="navbar-nav pt-lg-3">
              <li class="nav-item">
                <a class="nav-link" href="/dashboard" >
                  <span class="nav-link-icon d-md-none d-lg-inline-block">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" /></svg>
                  </span>
                  <span class="nav-link-title">
                    Dashboard
                  </span>
                </a>
              </li>
              {{-- !! TAMBAHKAN MENU BARU DI SINI !! --}}
              <li class="nav-item {{ request()->is('areas*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('areas.index') }}" >
                  <span class="nav-link-icon d-md-none d-lg-inline-block">
                    {{-- Ikon Peta --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-map-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 18.5l-3 -1.5l-6 3v-13l6 -3l6 3l6 -3v7.5" /><path d="M9 4v13" /><path d="M15 7v5" /><path d="M18 18m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M18 21v-1.5" /></svg>
                  </span>
                  <span class="nav-link-title">
                    Manajemen Area
                  </span>
                </a>
              </li>

              {{-- Menu-menu lain akan kita tambahkan di sini nanti --}}
            </ul>
          </div>
        </div>
      </aside>

      <div class="page-wrapper">

        {{-- Ini adalah "slot" di mana konten utama setiap halaman akan dimasukkan --}}
        @yield('content')

        <footer class="footer footer-transparent d-print-none">
          <div class="container-xl">
            <div class="row text-center align-items-center flex-row-reverse">
              <div class="col-lg-auto ms-lg-auto">
                {{-- Footer links --}}
              </div>
              <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                <ul class="list-inline list-inline-dots mb-0">
                  <li class="list-inline-item">
                    Copyright &copy; {{ date('Y') }} Kleening.id
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </footer>
      </div>
    </div>

    {{-- Pindahkan @vite ke bawah untuk memastikan DOM ready --}}
    @vite(['resources/js/app.js'])
  </body>
</html>