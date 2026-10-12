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

<!-- Menú de Navegación Responsive -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm">
    <div class="container-fluid px-2 px-md-4">
        <a class="navbar-brand fw-bold text-white tracking-wide fs-5 fs-md-4" href="#">🎮 CHIQUIPARK</a>

        <!-- Botón siempre visible en celular -->
        <button class="btn btn-success fw-bold px-2 px-md-4 ms-auto me-2" id="btn-nueva-sesion">+ Iniciar Sesión</button>

        <!-- Botón Hamburguesa restaurado -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 fs-5 text-center text-lg-start mt-3 mt-lg-0">
                <li class="nav-item"><a class="nav-link active text-info fw-bold" href="#">🎯 Tablero</a></li>
                <li class="nav-item"><a class="nav-link text-warning fw-bold" href="#" id="menu-caja">💰 Caja y Agente</a></li>
                <li class="nav-item"><a class="nav-link text-info fw-bold" href="#" id="menu-inventario">📦 Inventario</a></li>
            </ul>
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
