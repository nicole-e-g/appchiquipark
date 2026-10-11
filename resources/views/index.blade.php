<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CHIQUIPARK POS</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Librerías Core -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- TUS ESTILOS -->
    <link href="{{ asset('css/estilos.css') }}" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Menú de Navegación -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold text-white tracking-wide" href="#">🎮 CHIQUIPARK</a>
        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 fs-5">
                <li class="nav-item"><a class="nav-link active text-info fw-bold" href="#">🎯 Tablero POS</a></li>
                <li class="nav-item"><a class="nav-link text-warning fw-bold" href="#" id="menu-caja" style="cursor:pointer;">💰 Gestión de Caja</a></li>
                <li class="nav-item"><a class="nav-link text-info fw-bold" href="#" id="menu-inventario" style="cursor:pointer;">📦 Inventario</a></li>
            </ul>
            <button class="btn btn-success fw-bold px-4" id="btn-nueva-sesion">+ Iniciar Sesión de Juego</button>
        </div>
    </div>
</nav>

<!-- Tablero Principal -->
<div class="container-fluid">
    <div class="row" id="tablero-sesiones">
        <!-- Tarjetas inyectadas por JS -->
    </div>
</div>

<!-- INYECCIÓN DE MODALES -->
@include('modales.iniciar-sesion')
@include('modales.cuenta')
@include('modales.caja')
@include('modales.inventario')

<!-- TU JAVASCRIPT -->
<script src="{{ asset('js/pos.js?v=2') }}"></script>
</body>
</html>
