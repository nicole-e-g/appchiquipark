<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CHIQUIPARK</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Clases para el semáforo de tiempo */
        .bg-verde { background-color: #d1e7dd !important; border-color: #badbcc; }
        .bg-amarillo { background-color: #fff3cd !important; border-color: #ffecb5; }
        .bg-rojo { background-color: #f8d7da !important; border-color: #f5c2c7; color: #842029; }
        .sesion-card { transition: all 0.3s ease; }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">Ludoteca POS - Tablero de Control</span>
        <button class="btn btn-success" id="btn-nueva-sesion">+ Iniciar Sesión de Juego</button>
    </div>
</nav>

<div class="container-fluid">
    <!-- Aquí se dibujarán las tarjetas de los niños -->
    <div class="row" id="tablero-sesiones">
        <!-- Las tarjetas se inyectarán por JavaScript -->
    </div>
</div>

<script>
    $(document).ready(function() {
        let sesiones = [];

        // 1. Obtener datos del backend
        function cargarTablero() {
            $.get('/api/sesiones/activas', function(data) {
                sesiones = data;
                dibujarTarjetas();
            });
        }

        // 2. Dibujar el HTML de las tarjetas
        function dibujarTarjetas() {
            let html = '';
            sesiones.forEach(sesion => {
                html += `
            <div class="col-md-3 mb-4">
                <div class="card sesion-card" id="card-${sesion.id}" data-inicio="${sesion.inicio_sesion}" data-minutos="${sesion.minutos_totales}">
                    <div class="card-body">
                        <h5 class="card-title fw-bold">${sesion.nombre_nino || 'Venta Rápida'}</h5>
                        <h2 class="temporizador display-6 fw-bold text-center my-3">00:00</h2>
                        <p class="mb-1">Deuda: S/ <strong>${(sesion.total - sesion.monto_pagado).toFixed(2)}</strong></p>
                        <hr>
                        <button class="btn btn-primary btn-sm w-100" onclick="abrirCuenta(${sesion.id})">Ver Cuenta / Cobrar</button>
                    </div>
                </div>
            </div>`;
            });
            $('#tablero-sesiones').html(html);
        }

        // 3. El motor del tiempo (Se ejecuta cada 1 segundo)
        setInterval(function() {
            $('.sesion-card').each(function() {
                let card = $(this);
                let inicioStr = card.data('inicio'); // Ej: "2026-10-07 14:00:00"
                let minutosComprados = parseInt(card.data('minutos'));

                if(!inicioStr || isNaN(minutosComprados)) return;

                // Cálculos de tiempo
                let fechaInicio = new Date(inicioStr.replace(/-/g, '/')); // Compatible con Safari/iOS
                let fechaFin = new Date(fechaInicio.getTime() + (minutosComprados * 60000));
                let ahora = new Date();

                let diferenciaMs = fechaFin - ahora;
                let minutosRestantes = Math.floor(diferenciaMs / 60000);
                let segundosRestantes = Math.floor((diferenciaMs % 60000) / 1000);

                // Formatear texto (Ej: -05:30 si se pasó, o 14:05 si falta)
                let signo = diferenciaMs < 0 ? "-" : "";
                let txtMin = String(Math.abs(minutosRestantes)).padStart(2, '0');
                let txtSeg = String(Math.abs(segundosRestantes)).padStart(2, '0');
                card.find('.temporizador').text(`${signo}${txtMin}:${txtSeg}`);

                // Lógica de colores (Semáforo)
                card.removeClass('bg-verde bg-amarillo bg-rojo text-white');
                if (minutosRestantes > 9) {
                    card.addClass('bg-verde'); // Más de 10 min
                } else if (minutosRestantes >= 0 && minutosRestantes <= 9) {
                    card.addClass('bg-amarillo'); // Entre 0 y 9 min
                } else {
                    card.addClass('bg-rojo text-white'); // Tiempo agotado (negativo)
                }
            });
        }, 1000); // 1000 ms = 1 segundo

        // Iniciar al cargar la página
        cargarTablero();

        // Actualizar el tablero cada 30 segundos por si otro cajero hace cambios
        setInterval(cargarTablero, 30000);
    });

    function abrirCuenta(id) {
        alert("Aquí abriremos el modal para agregar productos o cobrar con Yape/Efectivo para la venta: " + id);
    }
</script>
</body>
</html>
