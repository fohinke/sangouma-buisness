<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', config('app.name','Magasin'))</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root { --navbar-h: 64px; }
    body { background: #f6f8fb; }
    .sidebar { width: 260px; background: #0d1b2a; color: #fff; position: fixed; top: 0; bottom: 0; padding-top: 60px; }
    .sidebar a { color: #cfd7e3; text-decoration: none; display: block; padding: .6rem 1rem; }
    .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,.08); color: #fff; }
    .content-wrapper { margin-left: 260px; padding-top: calc(var(--navbar-h) + 48px); }
    .toolbar { position: sticky; top: var(--navbar-h); z-index: 1020; background: #fff; padding: 10px 12px; border-bottom: 1px solid #e9ecef; margin-bottom: 12px; box-shadow: 0 2px 6px rgba(0,0,0,.04); border-radius: 8px; }
    .navbar-brand { font-weight: 600; }
    .card-kpi { border: 0; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,.06); }
  </style>
  @stack('head')
  @vite(['resources/js/app.js'])
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  @livewireStyles
  @stack('styles')
  @stack('scripts-head')
  @yield('head')
  @yield('styles')
  @yield('scripts-head')
  <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.css">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
      <a class="navbar-brand" href="{{ url('/') }}"><i class="bi bi-shop"></i> Sangouma</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topnav"><span class="navbar-toggler-icon"></span></button>
      <div class="collapse navbar-collapse" id="topnav">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
        </ul>
        <ul class="navbar-nav ms-auto">
          @auth
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                  <a class="dropdown-item" href="{{ route('profile.edit') }}">
                    <i class="bi bi-person"></i> Profil
                  </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item">
                      <i class="bi bi-box-arrow-right"></i> Déconnexion
                    </button>
                  </form>
                </li>
              </ul>
            </li>
          @endauth
        </ul>
      </div>
    </div>
  </nav>

  <aside class="sidebar">
    <div class="px-3 mb-2 text-uppercase small opacity-75">Navigation</div>
    <a href="{{ route('suppliers.index') }}" class="@if(request()->is('suppliers*')) active @endif"><i class="bi bi-truck"></i> Fournisseurs</a>
    <a href="{{ route('products.index') }}" class="@if(request()->is('products*')) active @endif"><i class="bi bi-box-seam"></i> Produits</a>
    <a href="{{ route('clients.index') }}" class="@if(request()->is('clients*')) active @endif"><i class="bi bi-people"></i> Clients</a>
    <a href="{{ route('purchase-orders.index') }}" class="@if(request()->is('purchase-orders*')) active @endif"><i class="bi bi-bag-check"></i> Commandes fournisseurs</a>
    <a href="{{ route('sales.index') }}" class="@if(request()->is('sales*')) active @endif"><i class="bi bi-cash-coin"></i> Ventes</a>
    @can('manage users')
      <a href="{{ route('users.index') }}" class="@if(request()->is('users*')) active @endif"><i class="bi bi-people-gear"></i> Utilisateurs</a>
    @endcan
  </aside>

  <main class="content-wrapper">
    <div class="container-fluid">
      @if(session('success'))
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
          <div id="appToastSuccess" class="toast align-items-center text-bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
              <div class="toast-body">{{ session('success') }}</div>
              <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
          </div>
        </div>
      @endif
      @yield('content')
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  @livewireScripts
  @stack('body')
  @yield('body')
  <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
  <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function(){
      const ids = ['salesTable','poTable'];
      ids.forEach(id => {
        const el = document.getElementById(id);
        if (el && !el.dataset.dtApplied) { new DataTable(el); el.dataset.dtApplied = '1'; }
      });
    });
  </script>
</body>
</html>
