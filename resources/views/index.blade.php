<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CHIQUIPARK</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- ¡NUEVO! Bootstrap 5 JS Bundle (Requerido para que los modales funcionen) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            background-color: #f3f4f6 !important;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        .sesion-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .sesion-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .bg-verde { background-color: #ecfdf5 !important; border-top: 5px solid #10b981; }
        .bg-verde .temporizador { color: #047857; }
        .bg-amarillo { background-color: #fffbeb !important; border-top: 5px solid #f59e0b; }
        .bg-amarillo .temporizador { color: #b45309; }
        .bg-rojo { background-color: #fef2f2 !important; border-top: 5px solid #ef4444; }
        .bg-rojo .temporizador { color: #b91c1c; }
        .modal-content { border-radius: 16px; border: none; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
        .modal-header { border-top-left-radius: 16px; border-top-right-radius: 16px; border-bottom: none; }
        .btn { border-radius: 8px; font-weight: 500; letter-spacing: 0.025em; padding: 0.5rem 1rem; }
        .temporizador { font-variant-numeric: tabular-nums; }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold text-white tracking-wide" href="#">🎮 CHIQUIPARK</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 fs-5">
                <li class="nav-item">
                    <a class="nav-link active text-info fw-bold" href="#">🎯 Tablero POS</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-warning fw-bold" href="#" id="menu-caja" style="cursor:pointer;">💰 Gestión de Caja</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-secondary" href="#">📦 Inventario (Pronto)</a>
                </li>
            </ul>
            <button class="btn btn-success fw-bold px-4" id="btn-nueva-sesion">+ Iniciar Sesión de Juego</button>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row" id="tablero-sesiones">
        <!-- Tarjetas inyectadas por JS -->
    </div>
</div>

<!-- MODAL: Iniciar Sesión de Juego -->
<div class="modal fade" id="modalIniciarSesion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Nueva Sesión de Juego</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nombre del Niño (o Cliente)</label>
                    <input type="text" class="form-control" id="nombreNino" placeholder="Ej. Mateo">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Tiempo Inicial</label>
                    <select class="form-select" id="productoTiempo">
                        <option value="1">30 Minutos (S/ 10.00)</option>
                        <option value="2">1 Hora (S/ 18.00)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btn-guardar-sesion">Iniciar Temporizador</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Cuenta y Consumos -->
<div class="modal fade" id="modalCuenta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Cuenta Activa: <span id="cuenta-nombre" class="fw-bold"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="cuenta-venta-id">

                <div class="p-3 mb-3 bg-light border rounded">
                    <h6 class="fw-bold text-secondary">Agregar Consumo</h6>
                    <div class="row g-2 align-items-center">
                        <div class="col-sm-6">
                            <select class="form-select" id="productoConsumo">
                                <option value="3">Jugo en caja (S/ 3.00)</option>
                                <option value="4">Galleta (S/ 1.50)</option>
                                <option value="1">Tiempo Extra: 30 Minutos (S/ 10.00)</option>
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <input type="number" class="form-control" id="cantidadConsumo" value="1" min="1" placeholder="Cant.">
                        </div>
                        <div class="col-sm-4">
                            <button class="btn btn-outline-primary w-100" id="btn-agregar-consumo">+ Agregar a la Cuenta</button>
                        </div>
                    </div>
                </div>

                <!-- Sección: Lista de Consumos -->
                <div class="mb-3">
                    <h6 class="fw-bold text-secondary">Detalle de la Cuenta</h6>
                    <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                        <table class="table table-sm table-striped border align-middle">
                            <thead class="table-light sticky-top">
                            <tr>
                                <th class="text-center" width="10%">Cant.</th>
                                <th>Producto</th>
                                <th class="text-end" width="20%">Subtotal</th>
                                <th class="text-center" width="20%">Estado</th>
                            </tr>
                            </thead>
                            <tbody id="lista-consumos">
                            <!-- Se llena automáticamente con JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="alert alert-warning mb-3">
                    <div class="d-flex justify-content-between fs-5">
                        <span>Total: <strong>S/ <span id="cuenta-total">0.00</span></strong></span>
                        <span>Abonado: <strong>S/ <span id="cuenta-pagado">0.00</span></strong></span>
                        <span class="text-danger">Deuda: <strong>S/ <span id="cuenta-deuda">0.00</span></strong></span>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-sm-4">
                        <div class="input-group">
                            <span class="input-group-text">S/</span>
                            <input type="number" class="form-control" id="montoPago" placeholder="Monto">
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <select class="form-select" id="metodoPago">
                            <option value="efectivo">Efectivo</option>
                            <option value="yape">Yape</option>
                            <option value="plin">Plin</option>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <button class="btn btn-success w-100" id="btn-registrar-pago">Abonar / Pagar</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-danger px-4 fw-bold" id="btn-cobrar-todo">Cerrar Cuenta y Cobrar Todo</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Gestión de Caja -->
<div class="modal fade" id="modalCaja" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold">💰 Control de Caja</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" id="caja-loading">
                <div class="spinner-border text-warning my-4" role="status"></div>
                <p>Consultando estado de caja...</p>
            </div>

            <!-- Vista 1: Caja Cerrada -->
            <div class="modal-body text-center d-none" id="caja-cerrada-view">
                <h4 class="text-danger mb-3">La caja está cerrada</h4>
                <p class="text-muted">Abre la caja para empezar a registrar ventas.</p>
                <div class="input-group mb-3 w-75 mx-auto">
                    <span class="input-group-text">S/</span>
                    <input type="number" class="form-control" id="montoApertura" placeholder="Monto base (Sencillo)" value="0">
                </div>
                <button class="btn btn-success w-75 fw-bold" id="btn-abrir-caja">Abrir Caja Ahora</button>
            </div>

            <!-- Vista 2: Caja Abierta -->
            <div class="modal-body d-none" id="caja-abierta-view">
                <div class="row text-center mb-3 g-1">
                    <div class="col-3">
                        <div class="p-2 bg-light border rounded">
                            <small class="text-muted d-block">Base</small>
                            <span class="fw-bold" id="lbl-caja-base">S/ 0.00</span>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 bg-light border rounded">
                            <small class="text-success d-block">+ Efectivo</small>
                            <span class="fw-bold" id="lbl-caja-ingreso-efectivo">S/ 0.00</span>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 bg-light border rounded">
                            <small class="text-primary d-block">Yape/Plin</small>
                            <span class="fw-bold" id="lbl-caja-yape">S/ 0.00</span>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 bg-danger text-white rounded">
                            <small class="d-block">- Egresos</small>
                            <span class="fw-bold" id="lbl-caja-egresos">S/ 0.00</span>
                        </div>
                    </div>
                </div>

                <div class="alert alert-success text-center fs-4 shadow-sm border-success mb-3">
                    Efectivo físico en Cajón<br>
                    <strong>S/ <span id="lbl-caja-efectivo">0.00</span></strong>
                </div>

                <div class="border rounded p-3 text-start mb-2 bg-light">
                    <h6 class="fw-bold text-secondary">Registrar Salida de Dinero</h6>
                    <div class="input-group">
                        <input type="text" class="form-control" id="conceptoEgreso" placeholder="Concepto (Ej: Hielo)">
                        <span class="input-group-text">S/</span>
                        <input type="number" class="form-control" id="montoEgreso" style="max-width: 100px;">
                        <button class="btn btn-danger" id="btn-guardar-egreso">Guardar</button>
                    </div>
                </div>

                <!-- Historial de Egresos para poder eliminarlos -->
                <div class="table-responsive" style="max-height: 150px;">
                    <table class="table table-sm table-hover align-middle mb-0 text-start">
                        <tbody id="lista-historial-egresos">
                        <!-- Inyectado por JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer d-none justify-content-between" id="caja-footer-abierta">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Seguir Trabajando</button>
                <button type="button" class="btn btn-dark fw-bold" id="btn-cerrar-caja">Cerrar Caja (Fin de Turno)</button>
            </div>
        </div>
    </div>
</div>

<script>
    // 0. Configurar jQuery para enviar siempre el Token de Seguridad
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    let sesiones = [];

    // 1. Obtener datos del backend
    function cargarTablero() {
        return $.get('/api/sesiones/activas', function(data) {
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

    // 3. Abrir la ventana de "Cuenta" (Debe estar global, fuera de document.ready y setInterval)
    // 3. Abrir la ventana de "Cuenta" y mostrar el detalle
    window.abrirCuenta = function(id) {
        let sesion = sesiones.find(s => s.id === id);
        if(!sesion) return;

        let total = parseFloat(sesion.total);
        let pagado = parseFloat(sesion.monto_pagado);
        let deuda = total - pagado;

        // Limpiar inputs para no arrastrar datos del niño anterior
        $('#productoConsumo').prop('selectedIndex', 0);
        $('#cantidadConsumo').val(1);

        // Llenar datos generales
        $('#cuenta-venta-id').val(sesion.id);
        $('#cuenta-nombre').text(sesion.nombre_nino || 'Cliente Rápido');
        $('#cuenta-total').text(total.toFixed(2));
        $('#cuenta-pagado').text(pagado.toFixed(2));
        $('#cuenta-deuda').text(deuda.toFixed(2));
        $('#montoPago').val(deuda > 0 ? deuda.toFixed(2) : '');
        $('#btn-cobrar-todo').prop('disabled', deuda <= 0);

        // Limpiar y llenar la tabla de consumos
        // Limpiar y llenar la tabla de consumos
        let htmlDetalles = '';
        if (sesion.detalles && sesion.detalles.length > 0) {
            sesion.detalles.forEach(detalle => {
                // Definir color del estado (Pendiente = Naranja, Pagado = Verde)
                let badgeEstado = detalle.estado_pago === 'pagado'
                    ? '<span class="badge bg-success">Pagado</span>'
                    : '<span class="badge bg-warning text-dark">Pendiente</span>';

                // Extraer el nombre del producto de forma segura
                let nombreProd = detalle.producto ? detalle.producto.nombre : 'Producto desconocido';

                // --- INICIO DEL CÓDIGO ACTUALIZADO (A) ---
                // Botón de eliminar (solo si no está pagado)
                let btnEliminar = detalle.estado_pago === 'pendiente'
                    ? `<button class="btn btn-sm btn-outline-danger py-0 px-2 ms-2" onclick="eliminarConsumo(${detalle.id}, ${sesion.id})">❌</button>`
                    : '';

                htmlDetalles += `
            <tr>
                <td class="text-center fw-bold">${detalle.cantidad}</td>
                <td>${nombreProd}</td>
                <td class="text-end">S/ ${parseFloat(detalle.subtotal).toFixed(2)}</td>
                <td class="text-center">${badgeEstado} ${btnEliminar}</td>
            </tr>
        `;
                // --- FIN DEL CÓDIGO ACTUALIZADO ---
            });
        } else {
            htmlDetalles = '<tr><td colspan="4" class="text-center text-muted">No hay consumos registrados</td></tr>';
        }
        $('#lista-consumos').html(htmlDetalles);

        $('#modalCuenta').modal('show');
    }

    $(document).ready(function() {

        // 4. El motor del tiempo (Se ejecuta cada 1 segundo SOLO para actualizar textos)
        setInterval(function() {
            $('.sesion-card').each(function() {
                let card = $(this);
                let inicioStr = card.data('inicio');
                let minutosComprados = parseInt(card.data('minutos'));

                if(!inicioStr || isNaN(minutosComprados)) return;

                let fechaInicio = new Date(inicioStr.replace(/-/g, '/'));
                let fechaFin = new Date(fechaInicio.getTime() + (minutosComprados * 60000));
                let ahora = new Date();

                let diferenciaMs = fechaFin - ahora;
                let minutosRestantes = Math.floor(diferenciaMs / 60000);
                let segundosRestantes = Math.floor((diferenciaMs % 60000) / 1000);

                let signo = diferenciaMs < 0 ? "-" : "";
                let txtMin = String(Math.abs(minutosRestantes)).padStart(2, '0');
                let txtSeg = String(Math.abs(segundosRestantes)).padStart(2, '0');
                card.find('.temporizador').text(`${signo}${txtMin}:${txtSeg}`);

                card.removeClass('bg-verde bg-amarillo bg-rojo text-white');
                if (minutosRestantes > 9) {
                    card.addClass('bg-verde');
                } else if (minutosRestantes >= 0 && minutosRestantes <= 9) {
                    card.addClass('bg-amarillo');
                } else {
                    card.addClass('bg-rojo text-white');
                }
            });
        }, 1000);

        // 5. Mostrar modal de Nueva Sesión
        $('#btn-nueva-sesion').click(function() {
            $('#nombreNino').val('');
            $('#modalIniciarSesion').modal('show');
        });

        // 6. Guardar Nueva Sesión en el Backend
        $('#btn-guardar-sesion').click(function() {
            $(this).prop('disabled', true).text('Iniciando...');

            $.post('/api/sesiones/iniciar', {
                nombre_nino: $('#nombreNino').val(),
                producto_id: $('#productoTiempo').val()
            }, function(response) {
                $('#modalIniciarSesion').modal('hide');
                $('#btn-guardar-sesion').prop('disabled', false).text('Iniciar Temporizador');
                cargarTablero();
            }).fail(function() {
                alert("Ocurrió un error. Revisa la consola.");
                $('#btn-guardar-sesion').prop('disabled', false).text('Iniciar Temporizador');
            });
        });

        // 7. Agregar Consumo
        $('#btn-agregar-consumo').click(function() {
            let ventaId = parseInt($('#cuenta-venta-id').val());
            let btn = $(this);

            btn.prop('disabled', true).text('Agregando...');

            $.post(`/api/ventas/${ventaId}/consumo`, {
                producto_id: $('#productoConsumo').val(),
                cantidad: $('#cantidadConsumo').val()
            }, function(response) {
                // Recargamos el tablero y cuando termine, refrescamos el modal actual
                cargarTablero().done(function() {
                    abrirCuenta(ventaId); // Actualiza la tabla y totales dinámicamente
                    $('#cantidadConsumo').val(1); // Resetea la cantidad a 1
                    btn.prop('disabled', false).text('+ Agregar a la Cuenta');
                });
            });
        });

        // 8. Registrar un Pago
        $('#btn-registrar-pago').click(function() {
            let ventaId = parseInt($('#cuenta-venta-id').val());
            let monto = parseFloat($('#montoPago').val());
            let btn = $(this);

            if(isNaN(monto) || monto <= 0) {
                alert("Ingresa un monto válido");
                return;
            }

            btn.prop('disabled', true).text('Procesando...');

            $.post(`/api/ventas/${ventaId}/pago`, {
                monto: monto,
                metodo: $('#metodoPago').val()
            }, function(response) {
                // Recargamos el tablero y cuando termine, refrescamos el modal actual
                cargarTablero().done(function() {
                    abrirCuenta(ventaId); // Actualiza la tabla y la deuda dinámicamente
                    btn.prop('disabled', false).text('Abonar / Pagar');
                    // Nota: El monto sugerido se auto-calcula dentro de abrirCuenta()
                });
            });
        });

        // 9. Cobrar toda la deuda
        $('#btn-cobrar-todo').click(function() {
            let ventaId = $('#cuenta-venta-id').val();
            let metodo = $('#metodoPago').val();

            if(confirm("¿Seguro que deseas liquidar toda la cuenta pendiente de esta sesión?")) {
                $.post(`/api/ventas/${ventaId}/cobrar-todo`, {
                    metodo_pago: metodo
                }, function(response) {
                    $('#modalCuenta').modal('hide');
                    cargarTablero();
                });
            }
        });

        // Iniciar al cargar la página
        cargarTablero();

        // Actualizar el tablero cada 30 segundos
        setInterval(cargarTablero, 30000);
    });

    // ==========================================
    // MÓDULO DE CAJA
    // ==========================================

    // Abrir el modal desde el menú superior
    $('#menu-caja').click(function(e) {
        e.preventDefault();
        $('#modalCaja').modal('show');
        consultarCaja();
    });

    // Función para consultar si la caja está abierta o cerrada
    function consultarCaja() {
        $('#caja-loading').removeClass('d-none');
        $('#caja-cerrada-view, #caja-abierta-view, #caja-footer-abierta').addClass('d-none');

        $.get('/api/caja/estado', function(data) {
            $('#caja-loading').addClass('d-none');

            if (data.abierta) {
                $('#caja-abierta-view, #caja-footer-abierta').removeClass('d-none');
                $('#lbl-caja-base').text('S/ ' + parseFloat(data.caja.monto_inicial).toFixed(2));
                $('#lbl-caja-ingreso-efectivo').text('S/ ' + parseFloat(data.ingresos_efectivo).toFixed(2));
                $('#lbl-caja-yape').text('S/ ' + parseFloat(data.ingresos_yape).toFixed(2));
                $('#lbl-caja-egresos').text('S/ ' + parseFloat(data.egresos).toFixed(2));
                $('#lbl-caja-efectivo').text(parseFloat(data.efectivo_esperado).toFixed(2));

                // Dibujar historial de egresos
                let htmlEgresos = '';
                if(data.lista_egresos.length > 0) {
                    data.lista_egresos.forEach(egreso => {
                        htmlEgresos += `
                            <tr>
                                <td>${egreso.concepto}</td>
                                <td class="text-end fw-bold text-danger">- S/ ${parseFloat(egreso.monto).toFixed(2)}</td>
                                <td class="text-end"><button class="btn btn-sm btn-outline-secondary py-0" onclick="eliminarEgreso(${egreso.id})">🗑️</button></td>
                            </tr>
                        `;
                    });
                } else {
                    htmlEgresos = '<tr><td class="text-center text-muted">No hay egresos registrados</td></tr>';
                }
                $('#lista-historial-egresos').html(htmlEgresos);
            }
        });
    }

    // Botón para Abrir Caja
    $('#btn-abrir-caja').click(function() {
        let monto = $('#montoApertura').val();
        $(this).prop('disabled', true).text('Abriendo...');

        $.post('/api/caja/abrir', { monto_inicial: monto }, function(res) {
            $('#btn-abrir-caja').prop('disabled', false).text('Abrir Caja Ahora');
            consultarCaja(); // Refrescar el modal
        });
    });

    // Botón para Registrar Egreso
    $('#btn-guardar-egreso').click(function() {
        let concepto = $('#conceptoEgreso').val();
        let monto = $('#montoEgreso').val();

        if (!concepto || monto <= 0) {
            alert("Ingresa un concepto y un monto válido.");
            return;
        }

        $(this).prop('disabled', true).text('Guardando...');

        $.post('/api/caja/egreso', { concepto: concepto, monto: monto }, function(res) {
            $('#conceptoEgreso').val('');
            $('#montoEgreso').val('');
            $('#btn-guardar-egreso').prop('disabled', false).text('Registrar Egreso');
            consultarCaja(); // Refresca los montos automáticamente
        });
    });

    // Botón para Cerrar Caja (Fin de turno)
    $('#btn-cerrar-caja').click(function() {
        if(confirm("¿Estás seguro de cerrar la caja? Al hacerlo no podrás cobrar ni iniciar nuevas sesiones hasta abrir otra.")) {
            $.post('/api/caja/cerrar', function() {
                $('#modalCaja').modal('hide');
                alert("Caja cerrada correctamente. El sistema está bloqueado para nuevas ventas.");
                cargarTablero(); // Refrescar tablero
            });
        }
    });

    // Función global para eliminar consumo
    window.eliminarConsumo = function(detalleId, ventaId) {
        if(confirm("¿Anular este consumo?")) {
            $.ajax({
                url: `/api/ventas/consumos/${detalleId}`,
                type: 'DELETE',
                success: function(res) {
                    cargarTablero().done(function() {
                        abrirCuenta(ventaId); // Refresca el modal para mostrar los nuevos totales
                    });
                },
                error: function() { alert("No se pudo anular. Posiblemente ya esté pagado."); }
            });
        }
    }

    // Función global para eliminar egreso
    window.eliminarEgreso = function(egresoId) {
        if(confirm("¿Anular este egreso? El monto regresará a la caja.")) {
            $.ajax({
                url: `/api/caja/egresos/${egresoId}`,
                type: 'DELETE',
                success: function(res) {
                    consultarCaja(); // Refresca los montos de caja automáticamente
                }
            });
        }
    }
</script>
</body>
</html>
