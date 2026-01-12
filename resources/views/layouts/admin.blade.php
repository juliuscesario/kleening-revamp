<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <meta http-equiv="X-UA-Compatible" content="ie=edge" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ \App\Models\AppSetting::get('app_name', config('app.name')) }} - @yield('title')</title>

  @vite(['resources/css/app.css'])

  @auth
    <script>
      const sessionToken = "{{ session('api_token') }}";
      if (sessionToken) {
        localStorage.setItem('auth_token', sessionToken);
      } else if (!localStorage.getItem('auth_token')) {
        // If no token in session (e.g. Remember Me) and no token in localStorage, fetch one.
        fetch('/auth/get-token', {
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
          .then(response => {
            if (response.ok) return response.json();
            throw new Error('Failed to fetch token');
          })
          .then(data => {
            if (data.token) {
              localStorage.setItem('auth_token', data.token);
              // Optional: Trigger an event or reload dependent components if needed
              // But usually the next fetch request will just pick it up.
            }
          })
          .catch(err => console.error('Could not auto-fetch API token:', err));
      }
    </script>
  @endauth

  @stack('styles')
  <style>
    /* Notification Dropdown Styles */
    .notification-dropdown-menu {
      min-width: 25rem;
    }

    @media (max-width: 576px) {
      .notification-dropdown-menu.dropdown-menu-end {
        min-width: 95vw;
        max-width: 95vw;
        left: auto !important;
        right: 5px !important;
        transform: none !important;
      }
    }
  </style>
</head>

<body>
  <div class="page">
    <aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
      <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu"
          aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <h1 class="navbar-brand navbar-brand-autodark">
          <a href=".">
            @if(\App\Models\AppSetting::get('app_logo'))
              <img src="{{ asset('storage/' . \App\Models\AppSetting::get('app_logo')) }}" alt="Logo"
                class="navbar-brand-image">
            @else
              {{ \App\Models\AppSetting::get('app_name', config('app.name')) }}
            @endif
          </a>
        </h1>
        <div class="collapse navbar-collapse" id="sidebar-menu">
          <ul class="navbar-nav pt-lg-3">
            <li class="nav-item">
              <a class="nav-link" href="/dashboard">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24"
                    stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M5 12l-2 0l9 -9l9 9l-2 0" />
                    <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" />
                    <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" />
                  </svg>
                </span>
                <span class="nav-link-title">Dashboard</span>
              </a>
            </li>

            {{-- NEW "MASTER" DROPDOWN MENU --}}
            @if(Auth::user()->role === 'owner' || Auth::user()->role === 'admin' || Auth::user()->role === 'co_owner')
              <li
                class="nav-item dropdown {{ request()->is('areas*') || request()->is('service-categories*') || request()->is('staff*') || request()->is('services*') ? 'active' : '' }}">
                <a class="nav-link dropdown-toggle" href="#navbar-master" data-bs-toggle="dropdown"
                  data-bs-auto-close="false" role="button" aria-expanded="false">
                  <span class="nav-link-icon d-md-none d-lg-inline-block">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24"
                      stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                      <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                      <path d="M12 6m-8 0a8 3 0 1 0 16 0a8 3 0 1 0 -16 0" />
                      <path d="M4 6v6a8 3 0 0 0 16 0v-6" />
                      <path d="M4 12v6a8 3 0 0 0 16 0v-6" />
                    </svg>
                  </span>
                  <span class="nav-link-title">Master Data</span>
                </a>
                <div
                  class="dropdown-menu {{ request()->is('areas*') || request()->is('service-categories*') || request()->is('staff*') || request()->is('services*') ? 'show' : '' }}">
                  <div class="dropdown-menu-columns">
                    <div class="dropdown-menu-column">
                      <a class="dropdown-item {{ request()->is('areas*') ? 'active' : '' }}"
                        href="{{ route('web.areas.index') }}">
                        Manajemen Area
                      </a>
                      <a class="dropdown-item {{ request()->is('service-categories*') ? 'active' : '' }}"
                        href="{{ route('web.service-categories.index') }}">
                        Kategori Layanan
                      </a>
                      <a class="dropdown-item {{ request()->is('staff*') ? 'active' : '' }}"
                        href="{{ route('web.staff.index') }}">
                        Manajemen Staff
                      </a>
                      <a class="dropdown-item {{ request()->is('services*') ? 'active' : '' }}"
                        href="{{ route('web.services.index') }}">
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
              <li
                class="nav-item dropdown {{ request()->is('customers*') || request()->is('addresses*') ? 'active' : '' }}">
                <a class="nav-link dropdown-toggle" href="#navbar-customer" data-bs-toggle="dropdown"
                  data-bs-auto-close="false" role="button" aria-expanded="false">
                  <span class="nav-link-icon d-md-none d-lg-inline-block">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24"
                      stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                      <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                      <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                      <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                      <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                      <path d="M21 21v-2a4 4 0 0 0 -3 -3.85" />
                    </svg>
                  </span>
                  <span class="nav-link-title">Customer Data</span>
                </a>
                <div class="dropdown-menu {{ request()->is('customers*') || request()->is('addresses*') ? 'show' : '' }}">
                  <div class="dropdown-menu-columns">
                    <div class="dropdown-menu-column">
                      <a class="dropdown-item {{ request()->is('customers*') ? 'active' : '' }}"
                        href="{{ route('web.customers.index') }}">
                        Manajemen Customer
                      </a>
                      <a class="dropdown-item {{ request()->is('addresses*') ? 'active' : '' }}"
                        href="{{ route('web.addresses.index') }}">
                        Manajemen Alamat
                      </a>
                    </div>
                  </div>
                </div>
              </li>
            @endif
            {{-- END OF CUSTOMER DROPDOWN --}}

            {{-- ORDER DROPDOWN --}}
            @if(Auth::user()->role === 'owner' || Auth::user()->role === 'admin' || Auth::user()->role === 'co_owner')
              <li class="nav-item dropdown {{ request()->is('service-orders*') ? 'active' : '' }}">
                <a class="nav-link dropdown-toggle" href="#navbar-transaction" data-bs-toggle="dropdown"
                  data-bs-auto-close="false" role="button" aria-expanded="false">
                  <span class="nav-link-icon d-md-none d-lg-inline-block">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-clipboard-text" width="24"
                      height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                      stroke-linecap="round" stroke-linejoin="round">
                      <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                      <path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2"></path>
                      <path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z"></path>
                      <path d="M9 12h6"></path>
                      <path d="M9 16h6"></path>
                    </svg>
                  </span>
                  <span class="nav-link-title">Order</span>
                </a>
                <div class="dropdown-menu {{ request()->is('service-orders*') ? 'show' : '' }}">
                  <div class="dropdown-menu-columns">
                    <div class="dropdown-menu-column">
                      <a class="dropdown-item {{ request()->is('service-orders*') ? 'active' : '' }}"
                        href="{{ route('web.service-orders.index') }}">
                        Service Orders
                      </a>
                    </div>
                  </div>
                </div>
              </li>
            @endif

            {{-- NEW TRANSACTION DROPDOWN --}}
            @if(Auth::user()->role === 'owner' || Auth::user()->role === 'admin' || Auth::user()->role === 'co_owner')
              <li
                class="nav-item dropdown {{ request()->is('invoices*') || request()->is('payments*') ? 'active' : '' }}">
                <a class="nav-link dropdown-toggle" href="#navbar-transaction-new" data-bs-toggle="dropdown"
                  data-bs-auto-close="false" role="button" aria-expanded="false">
                  <span class="nav-link-icon d-md-none d-lg-inline-block">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-receipt-2" width="24"
                      height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                      stroke-linecap="round" stroke-linejoin="round">
                      <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                      <path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2"></path>
                      <path d="M14 8h-2.5a1.5 1.5 0 0 0 0 3h1a1.5 1.5 0 0 1 0 3h-2.5m2 0v1.5m0 -9v1.5"></path>
                    </svg>
                  </span>
                  <span class="nav-link-title">Transaction</span>
                </a>
                <div class="dropdown-menu {{ request()->is('invoices*') || request()->is('payments*') ? 'show' : '' }}">
                  <div class="dropdown-menu-columns">
                    <div class="dropdown-menu-column">
                      <a class="dropdown-item {{ request()->is('invoices*') ? 'active' : '' }}"
                        href="{{ route('web.invoices.index') }}">
                        Invoices
                      </a>
                      <a class="dropdown-item {{ request()->is('payments*') ? 'active' : '' }}"
                        href="{{ route('web.payments.index') }}">
                        Payments
                      </a>
                    </div>
                  </div>
                </div>
              </li>
            @endif

            {{-- REPORTS DROPDOWN --}}
            @if(in_array(strtolower(Auth::user()->role), ['owner', 'co_owner', 'admin']))
              <li class="nav-item dropdown {{ request()->is('reports*') ? 'active' : '' }}">
                <a class="nav-link dropdown-toggle" href="#navbar-reports" data-bs-toggle="dropdown"
                  data-bs-auto-close="false" role="button"
                  aria-expanded="{{ request()->is('reports*') ? 'true' : 'false' }}">
                  <span class="nav-link-icon d-md-none d-lg-inline-block">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-chart-bar" width="24"
                      height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                      stroke-linecap="round" stroke-linejoin="round">
                      <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                      <path d="M3 12m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"></path>
                      <path d="M9 8m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"></path>
                      <path d="M15 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v14a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"></path>
                      <path d="M4 20l14 0"></path>
                    </svg>
                  </span>
                  <span class="nav-link-title">Laporan</span>
                </a>
                <div class="dropdown-menu {{ request()->is('reports*') ? 'show' : '' }}">
                  <div class="dropdown-menu-columns">
                    <div class="dropdown-menu-column">
                      <a class="dropdown-item {{ request()->is('reports/revenue*') ? 'active' : '' }}"
                        href="{{ route('web.reports.revenue') }}">
                        Laporan Pendapatan
                      </a>
                      <a class="dropdown-item {{ request()->is('reports/staff-performance*') ? 'active' : '' }}"
                        href="{{ route('web.reports.staff-performance') }}">
                        Laporan Kinerja Staff
                      </a>
                      <a class="dropdown-item {{ request()->is('reports/customer-growth*') ? 'active' : '' }}"
                        href="{{ route('web.reports.customer-growth') }}">
                        Laporan Pertumbuhan Pelanggan
                      </a>
                      <a class="dropdown-item {{ request()->is('reports/profitability*') ? 'active' : '' }}"
                        href="{{ route('web.reports.profitability') }}">
                        Laporan Profitabilitas
                      </a>
                      <a class="dropdown-item {{ request()->is('reports/staff-utilization*') ? 'active' : '' }}"
                        href="{{ route('web.reports.staff-utilization') }}">
                        Laporan Utilisasi Staff
                      </a>
                      <a class="dropdown-item {{ request()->is('reports/invoice-aging*') ? 'active' : '' }}"
                        href="{{ route('web.reports.invoice-aging') }}">
                        Laporan Umur Piutang
                      </a>
                    </div>
                  </div>
                </div>
              </li>
            @endif

            {{-- SYSTEM DROPDOWN --}}
            @if(in_array(strtolower(Auth::user()->role), ['owner', 'co_owner']))
              <li class="nav-item dropdown {{ request()->is('scheduler-logs*') ? 'active' : '' }}">
                <a class="nav-link dropdown-toggle" href="#navbar-system" data-bs-toggle="dropdown"
                  data-bs-auto-close="false" role="button"
                  aria-expanded="{{ request()->is('scheduler-logs*') ? 'true' : 'false' }}">
                  <span class="nav-link-icon d-md-none d-lg-inline-block">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-settings" width="24"
                      height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                      stroke-linecap="round" stroke-linejoin="round">
                      <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                      <path
                        d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1.002 .613 2.104 .613 3.106 0c.01 -.006 .02 -.012 .03 -.018z">
                      </path>
                      <path d="M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"></path>
                    </svg>
                  </span>
                  <span class="nav-link-title">System</span>
                </a>
                <div class="dropdown-menu {{ request()->is('scheduler-logs*') ? 'show' : '' }}">
                  <div class="dropdown-menu-columns">
                    <div class="dropdown-menu-column">
                      <a class="dropdown-item {{ request()->is('scheduler-logs*') ? 'active' : '' }}"
                        href="{{ route('scheduler-logs.index') }}">
                        Scheduler Logs
                      </a>
                      @if(Auth::user()->role === 'owner')
                        <a class="dropdown-item {{ request()->is('settings*') ? 'active' : '' }}"
                          href="{{ route('web.settings.index') }}">
                          App Settings
                        </a>
                      @endif
                    </div>
                  </div>
                </div>
              </li>
            @endif

          </ul>
        </div>
      </div>
    </aside>

    {{-- NEW HEADER WITH USER NAME AND LOGOUT BUTTON --}}
    <header class="navbar navbar-expand-md d-print-none">
      <div class="container-xl">
        <div class="navbar-nav flex-row order-md-last ms-auto">
          <div class="nav-item dropdown">
            <a href="#" id="notificationDropdown" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown"
              aria-label="Open notification menu">
              <span class="avatar avatar-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24"
                  stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                  <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                  <path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" />
                  <path d="M9 17v1a3 3 0 0 0 6 0v-1" />
                </svg>
                <span id="unread-count" class="badge bg-red badge-pill text-white d-none"></span>
              </span>
            </a>
            <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notification-dropdown-menu">
              <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                <h5 class="mb-0 px-2">Notifications</h5>
                <a href="#" id="mark-all-as-read" class="btn btn-sm btn-link">Mark all as read</a>
              </div>
              <ul class="nav nav-tabs nav-fill" id="notification-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="service-orders-tab" data-bs-toggle="tab"
                    data-bs-target="#service-orders" type="button" role="tab" aria-controls="service-orders"
                    aria-selected="true">Service Orders</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoices"
                    type="button" role="tab" aria-controls="invoices" aria-selected="false">Invoices</button>
                </li>
              </ul>
              <div class="tab-content" id="notification-tabs-content" style="max-height: 400px; overflow-y: auto;">
                <div class="tab-pane fade show active" id="service-orders" role="tabpanel"
                  aria-labelledby="service-orders-tab">
                  <div class="list-group list-group-flush" id="service-orders-notification-list">
                    {{-- Service Order notifications will be rendered here --}}
                  </div>
                </div>
                <div class="tab-pane fade" id="invoices" role="tabpanel" aria-labelledby="invoices-tab">
                  <div class="list-group list-group-flush" id="invoices-notification-list">
                    {{-- Invoice notifications will be rendered here --}}
                  </div>
                </div>
              </div>
              <div class="border-top">
                <a href="{{ route('web.notifications.index') }}" class="dropdown-item text-center text-primary">View all
                  notifications</a>
              </div>
            </div>
          </div>
          <div class="nav-item dropdown">
            <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown"
              aria-label="Open user menu">
              <span class="avatar avatar-sm">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</span>
              <div class="d-none d-xl-block ps-2">
                <div>{{ Auth::user()->name }}</div>
                <div class="mt-1 small text-muted">{{ ucfirst(Auth::user()->role) }}</div>
              </div>
            </a>
            <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <a href="{{ route('logout') }}" class="dropdown-item"
                  onclick="event.preventDefault(); this.closest('form').submit();">
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
                  Copyright &copy; {{ date('Y') }} {{ \App\Models\AppSetting::get('app_name', config('app.name')) }}
                </li>
              </ul>
            </div>
          </div>
        </div>
      </footer>
    </div>
  </div>
  @vite(['resources/js/app.js'])
  @stack('scripts')
</body>

</html>