<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CHIQUIPARK</title>
    <link rel="icon" href="{{ asset('img/chiqui2.png') }}" type="image/png">
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
<nav class="navbar navbar-expand-lg navbar-dark mb-4 shadow" style="background-color: #581c87;">
    <div class="container-fluid px-2 px-md-4">

        <!-- Logo en el Menú -->
        <a class="navbar-brand fw-bold text-white fs-5 fs-md-4 d-flex align-items-center" href="#">
            <div style="background-color: white; border-radius: 50%; padding: 2px; width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; margin-right: 10px;">
                <img src="{{ asset('img/chiqui2.png') }}" alt="Logo" style="max-height: 35px; max-width: 35px;">
            </div>
            CHIQUIPARK
        </a>

        <!-- Botón de Venta Libre -->
        <button class="btn fw-bold px-2 px-md-3 ms-auto me-2 text-white shadow-sm" style="background-color: #e11d48; border:none;" id="btn-venta-rapida">🛒 Venta Libre</button>

        <!-- Botón de Iniciar Sesión -->
        <button class="btn fw-bold px-2 px-md-3 me-2 text-white shadow-sm" style="background-color: #0ea5e9; border:none;" id="btn-nueva-sesion">+ Iniciar Juego</button>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 fs-5 text-center text-lg-start mt-3 mt-lg-0">
                <li class="nav-item"><a class="nav-link fw-bold" style="color: #fde047;" href="#" id="menu-caja">💰 Caja y Agente</a></li>
                <li class="nav-item"><a class="nav-link text-white fw-bold" href="#" id="menu-inventario">📦 Inventario</a></li>
            </ul>

            <form action="{{ route('logout') }}" method="POST" class="d-inline ms-lg-3 mt-2 mt-lg-0 text-center">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-light rounded-pill fw-bold px-3">Salir</button>
            </form>
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
