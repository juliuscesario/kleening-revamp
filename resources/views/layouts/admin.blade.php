<!doctype html>
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
            <a href=".">Kleening.id</a>
          </h1>
          <div class="collapse navbar-collapse" id="sidebar-menu">
            <ul class="navbar-nav pt-lg-3">
              <li class="nav-item">
                <a class="nav-link" href="/dashboard" >
                  <span class="nav-link-icon d-md-none d-lg-inline-block">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" /></svg>
                  </span>
                  <span class="nav-link-title">Dashboard</span>
                </a>
              </li>

              {{-- NEW "MASTER" DROPDOWN MENU --}}
              @if(Auth::user()->role === 'owner' || Auth::user()->role === 'admin' || Auth::user()->role === 'co_owner')
              <li class="nav-item dropdown {{ request()->is('areas*') || request()->is('service-categories*') || request()->is('staff*') || request()->is('services*') ? 'active' : '' }}">
                <a class="nav-link dropdown-toggle" href="#navbar-master" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false" >
                  <span class="nav-link-icon d-md-none d-lg-inline-block">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-database" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 6m-8 0a8 3 0 1 0 16 0a8 3 0 1 0 -16 0" /><path d="M4 6v6a8 3 0 0 0 16 0v-6" /><path d="M4 12v6a8 3 0 0 0 16 0v-6" /></svg>
                  </span>
                  <span class="nav-link-title">Master Data</span>
                </a>
                <div class="dropdown-menu {{ request()->is('areas*') || request()->is('service-categories*') || request()->is('staff*') || request()->is('services*') ? 'show' : '' }}">
                  <div class="dropdown-menu-columns">
                    <div class="dropdown-menu-column">
                      <a class="dropdown-item {{ request()->is('areas*') ? 'active' : '' }}" href="{{ route('web.areas.index') }}">
                        Manajemen Area
                      </a>
                      <a class="dropdown-item {{ request()->is('service-categories*') ? 'active' : '' }}" href="{{ route('web.service-categories.index') }}">
                        Kategori Layanan
                      </a>
                      <a class="dropdown-item {{ request()->is('staff*') ? 'active' : '' }}" href="{{ route('web.staff.index') }}">
                        Manajemen Staff
                      </a>
                      <a class="dropdown-item {{ request()->is('services*') ? 'active' : '' }}" href="{{ route('web.services.index') }}">
                        Manajemen Layanan
                      </a>
                    </div>
                  </div>
                </div>
              </li>
              @endif
              {{-- END OF "MASTER" DROPDOWN MENU --}}

              {{-- CUSTOMER DATA DROPDOWN --}}
              @if(Auth::user()->role === 'owner' || Auth::user()->role === 'admin' || Auth::user()->role === 'co_owner')
              <li class="nav-item dropdown {{ request()->is('customers*') || request()->is('addresses*') ? 'active' : '' }}">
                <a class="nav-link dropdown-toggle" href="#navbar-customer" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false" >
                  <span class="nav-link-icon d-md-none d-lg-inline-block">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-users" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
                  </span>
                  <span class="nav-link-title">Customer Data</span>
                </a>
                <div class="dropdown-menu {{ request()->is('customers*') || request()->is('addresses*') ? 'show' : '' }}">
                  <div class="dropdown-menu-columns">
                    <div class="dropdown-menu-column">
                      <a class="dropdown-item {{ request()->is('customers*') ? 'active' : '' }}" href="{{ route('web.customers.index') }}">
                        Manajemen Customer
                      </a>
                      <a class="dropdown-item {{ request()->is('addresses*') ? 'active' : '' }}" href="{{ route('web.addresses.index') }}">
                        Manajemen Alamat
                      </a>
                    </div>
                  </div>
                </div>
              </li>
              @endif
              {{-- END OF CUSTOMER DROPDOWN --}}

              {{-- TRANSACTION DROPDOWN --}}
              @if(Auth::user()->role === 'owner' || Auth::user()->role === 'admin' || Auth::user()->role === 'co_owner')
              <li class="nav-item dropdown {{ request()->is('service-orders*') ? 'active' : '' }}">
                <a class="nav-link dropdown-toggle" href="#navbar-transaction" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false" >
                  <span class="nav-link-icon d-md-none d-lg-inline-block">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-receipt-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2" /><path d="M14 8h-2.5a1.5 1.5 0 0 0 0 3h1a1.5 1.5 0 0 1 0 3h-2.5m2 0v1.5m0 -9v1.5" /></svg>
                  </span>
                  <span class="nav-link-title">Transaksi</span>
                </a>
                <div class="dropdown-menu {{ request()->is('service-orders*') ? 'show' : '' }}">
                  <div class="dropdown-menu-columns">
                    <div class="dropdown-menu-column">
                      <a class="dropdown-item {{ request()->is('service-orders*') ? 'active' : '' }}" href="{{ route('web.service-orders.index') }}">
                        Service Orders
                      </a>
                    </div>
                  </div>
                </div>
              </li>
              @endif
              {{-- END OF TRANSACTION DROPDOWN --}}
              
            </ul>
          </div>
        </div>
      </aside>

      {{-- NEW HEADER WITH USER NAME AND LOGOUT BUTTON --}}
      <header class="navbar navbar-expand-md d-print-none">
        <div class="container-xl">
          <div class="navbar-nav flex-row order-md-last ms-auto">
            <div class="nav-item dropdown">
              <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
                <span class="avatar avatar-sm">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</span>
                <div class="d-none d-xl-block ps-2">
                  <div>{{ Auth::user()->name }}</div>
                  <div class="mt-1 small text-muted">{{ ucfirst(Auth::user()->role) }}</div>
                </div>
              </a>
              <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <a href="{{ route('logout') }}" class="dropdown-item" onclick="event.preventDefault(); this.closest('form').submit();">
                    Logout
                  </a>
                </form>
              </div>
            </div>
          </div>
        </div>
      </header>
      {{-- END OF NEW HEADER --}}
      
      <div class="page-wrapper">
        @yield('content')
        <footer class="footer footer-transparent d-print-none">
          <div class="container-xl">
            <div class="row text-center align-items-center flex-row-reverse">
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
    @vite(['resources/js/app.js'])
  </body>
</html>`