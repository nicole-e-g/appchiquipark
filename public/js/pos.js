// ==========================================
// CONFIGURACIÓN INICIAL
// ==========================================
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

let sesiones = [];
let listaProductos = [];

// ==========================================
// 1. MÓDULO DE VENTAS Y TABLERO
// ==========================================

function cargarTablero() {
    return $.get('/api/sesiones/activas', function(data) {
        sesiones = data;
        dibujarTarjetas();
    });
}

function dibujarTarjetas() {
    let html = '';
    sesiones.forEach(sesion => {
        // CÁLCULO DE DEUDA
        let deudaReal = (sesion.total - sesion.monto_pagado).toFixed(2);

        // Si no hay minutos totales, es una venta de mostrador
        let visualTiempo = (!sesion.minutos_totales || sesion.minutos_totales == 0)
            ? '<h3 class="text-secondary text-center my-4">🛒 Compra Libre</h3>'
            : '<h2 class="temporizador display-6 fw-bold text-center my-3">00:00</h2>';

        html += `
        <div class="col-md-3 mb-4">
            <div class="card sesion-card" id="card-${sesion.id}" data-inicio="${sesion.inicio_sesion}" data-minutos="${sesion.minutos_totales}">
                <div class="card-body">
                    <h5 class="card-title fw-bold">${sesion.nombre_nino || 'Cliente'}</h5>
                    ${visualTiempo}
                    <p class="mb-1">Deuda: S/ <strong>${deudaReal}</strong></p>
                    <hr>
                    <button class="btn btn-primary btn-sm w-100" onclick="abrirCuenta(${sesion.id})">Ver Cuenta / Cobrar</button>
                </div>
            </div>
        </div>`;
    });
    $('#tablero-sesiones').html(html);
}

window.abrirCuenta = function(id) {
    let sesion = sesiones.find(s => s.id === id);
    if(!sesion) {
        $('#modalCuenta').modal('hide');
        return;
    }

    let total = parseFloat(sesion.total);
    let pagado = parseFloat(sesion.monto_pagado);
    let deuda = total - pagado;

    // Limpiar inputs
    $('#productoConsumo').prop('selectedIndex', 0);
    $('#cantidadConsumo').val(1);

    // Llenar datos de cabecera
    $('#cuenta-venta-id').val(sesion.id);
    $('#cuenta-nombre').text(sesion.nombre_nino || 'Cliente Rápido');
    $('#cuenta-total').text(total.toFixed(2));
    $('#cuenta-pagado').text(pagado.toFixed(2));
    $('#cuenta-deuda').text(deuda.toFixed(2));

    // Configurar pagos
    $('#montoPago').val(deuda > 0 ? deuda.toFixed(2) : '');
    let estaPagado = (deuda <= 0);
    $('#montoPago').prop('disabled', estaPagado);
    $('#metodoPago').prop('disabled', estaPagado);
    $('#btn-registrar-pago').prop('disabled', estaPagado);
    $('#btn-cobrar-todo').prop('disabled', estaPagado);
    $('#btn-finalizar-juego').prop('disabled', !estaPagado);

    // Llenar tabla de consumos
    let htmlDetalles = '';
    if (sesion.detalles && sesion.detalles.length > 0) {
        sesion.detalles.forEach(detalle => {
            let badgeEstado = detalle.estado_pago === 'pagado'
                ? '<span class="badge bg-success">Pagado</span>'
                : '<span class="badge bg-warning text-dark">Pendiente</span>';

            let nombreProd = detalle.producto ? detalle.producto.nombre : 'Producto desconocido';

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
        });
    } else {
        htmlDetalles = '<tr><td colspan="4" class="text-center text-muted">No hay consumos</td></tr>';
    }
    $('#lista-consumos').html(htmlDetalles);
    $('#modalCuenta').modal('show');
}

window.eliminarConsumo = function(detalleId, ventaId) {
    if(confirm("¿Anular este consumo?")) {
        $.ajax({
            url: `/api/ventas/consumos/${detalleId}`,
            type: 'DELETE',
            success: function() {
                cargarTablero().done(function() { abrirCuenta(ventaId); });
            },
            error: function() { alert("No se pudo anular. Posiblemente ya esté pagado."); }
        });
    }
}

// ==========================================
// 2. MÓDULO DE CAJA Y AGENTE
// ==========================================

function consultarCaja() {
    $('#caja-loading').removeClass('d-none');
    $('#caja-cerrada-view, #caja-abierta-view, #caja-footer-abierta').addClass('d-none');

    $.get('/api/caja/estado', function(data) {
        $('#caja-loading').addClass('d-none');

        if (data.abierta) {
            $('#caja-abierta-view, #caja-footer-abierta').removeClass('d-none');
            $('#lbl-caja-efectivo').text(parseFloat(data.efectivo_esperado).toFixed(2));
            $('#lbl-caja-yape').text(parseFloat(data.yape_esperado).toFixed(2));

            let htmlHistorial = '';
            if(data.lista_movimientos && data.lista_movimientos.length > 0) {
                data.lista_movimientos.reverse().forEach(mov => {
                    let color = mov.tipo === 'ingreso' ? 'text-success' : 'text-danger';
                    let signo = mov.tipo === 'ingreso' ? '+' : '-';
                    let badge = mov.metodo === 'yape' ? '<span class="badge bg-primary">Yape</span>' : '<span class="badge bg-success">Efectivo</span>';

                    htmlHistorial += `
                        <tr>
                            <td>${badge} <small>${mov.concepto}</small></td>
                            <td class="text-end fw-bold ${color}">${signo} S/ ${parseFloat(mov.monto).toFixed(2)}</td>
                            <td class="text-end"><button class="btn btn-sm btn-outline-secondary py-0" onclick="eliminarMov(${mov.id})">🗑️</button></td>
                        </tr>
                    `;
                });
            } else {
                htmlHistorial = '<tr><td colspan="3" class="text-center text-muted">Sin movimientos manuales</td></tr>';
            }
            $('#lista-historial-movimientos').html(htmlHistorial);

        } else {
            $('#caja-cerrada-view').removeClass('d-none');
        }
    });
}

window.eliminarMov = function(id) {
    if(confirm("¿Anular movimiento? Los saldos se revertirán.")) {
        $.ajax({ url: `/api/caja/movimientos/${id}`, type: 'DELETE', success: function() { consultarCaja(); } });
    }
}

// ==========================================
// 3. MÓDULO DE INVENTARIO
// ==========================================

function cargarProductos() {
    $.get('/api/productos', function(data) {
        listaProductos = data;
        dibujarTablaInventario();
        llenarSelectsVentas();
    });
}

function llenarSelectsVentas() {
    let optionsTiempo = '';
    let optionsGeneral = '';

    listaProductos.forEach(p => {
        if (p.estado == 1) {
            let texto = `${p.nombre} (S/ ${parseFloat(p.precio).toFixed(2)})`;
            if (p.es_tiempo == 1) optionsTiempo += `<option value="${p.id}">${texto}</option>`;
            optionsGeneral += `<option value="${p.id}">${texto}</option>`;
        }
    });

    $('#productoTiempo').html(optionsTiempo);
    $('#productoConsumo').html(optionsGeneral);
}

function dibujarTablaInventario() {
    let html = '';
    listaProductos.forEach(p => {
        let badgeEstado = p.estado == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
        let tipoTxt = p.es_tiempo == 1 ? `Tiempo (${p.minutos_otorgados} min)` : 'Consumo';
        let colorFila = p.estado == 0 ? 'table-secondary opacity-50' : '';

        html += `
            <tr class="${colorFila}">
                <td class="fw-bold">${p.nombre}</td>
                <td>S/ ${parseFloat(p.precio).toFixed(2)}</td>
                <td>${tipoTxt}</td>
                <td class="text-center">${badgeEstado}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-primary py-0 px-2" onclick="editarProducto(${p.id})">✏️</button>
                    <button class="btn btn-sm btn-outline-dark py-0 px-2" onclick="toggleEstadoProducto(${p.id})">🔄</button>
                </td>
            </tr>
        `;
    });
    $('#lista-inventario').html(html);
}

window.editarProducto = function(id) {
    let p = listaProductos.find(x => x.id == id);
    if(!p) return;

    $('#titulo-form-producto').text('Editar Producto');
    $('#inv-id').val(p.id);
    $('#inv-nombre').val(p.nombre);
    $('#inv-precio').val(p.precio);
    $('#inv-tipo').val(p.es_tiempo).trigger('change');
    if (p.es_tiempo == 1) $('#inv-minutos').val(p.minutos_otorgados);

    $('#btn-cancelar-edicion').removeClass('d-none');
    $('#btn-guardar-producto').text('Actualizar').removeClass('btn-primary').addClass('btn-warning');
}

function limpiarFormularioProducto() {
    $('#titulo-form-producto').text('Agregar Nuevo Producto');
    $('#inv-id, #inv-nombre, #inv-minutos').val('');
    $('#inv-precio').val('0.00');
    $('#inv-tipo').val('0').trigger('change');
    $('#btn-cancelar-edicion').addClass('d-none');
    $('#btn-guardar-producto').text('Guardar Producto').removeClass('btn-warning').addClass('btn-primary').prop('disabled', false);
}

window.toggleEstadoProducto = function(id) {
    $.post(`/api/productos/${id}/toggle`, function() { cargarProductos(); });
}

// ==========================================
// 4. EVENTOS (DOCUMENT READY)
// ==========================================
$(document).ready(function() {

    // Inicializadores
    cargarTablero();
    cargarProductos();
    setInterval(cargarTablero, 30000);

    // Motor del Tiempo
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
            if (minutosRestantes > 9) { card.addClass('bg-verde'); }
            else if (minutosRestantes >= 0 && minutosRestantes <= 9) { card.addClass('bg-amarillo'); }
            else { card.addClass('bg-rojo text-white'); }
        });
    }, 1000);

    // ---- EVENTOS DE TABLERO Y VENTAS ----
    $('#btn-nueva-sesion').click(function() {
        $('#nombreNino').val('');
        $('#modalIniciarSesion').modal('show');
    });

    $('#btn-venta-rapida').click(function() {
        let btn = $(this);
        btn.prop('disabled', true).text('...');

        $.post('/api/ventas/rapida', function() {
            cargarTablero();
            btn.prop('disabled', false).text('🛒 Venta Libre');
        }).fail(function(err) {
            alert(err.responseJSON ? err.responseJSON.error : "Error de conexión");
            btn.prop('disabled', false).text('🛒 Venta Libre');
        });
    });

    $('#btn-guardar-sesion').click(function() {
        $(this).prop('disabled', true).text('Iniciando...');$.post('/api/sesiones/iniciar', {
            nombre_nino: $('#nombreNino').val(),
            producto_id: $('#productoTiempo').val()
        }, function() {
            $('#modalIniciarSesion').modal('hide');
            $('#btn-guardar-sesion').prop('disabled', false).text('Iniciar Temporizador');
            cargarTablero();
        }).fail(function() {
            alert("Error. Recuerda abrir la caja primero.");
            $('#btn-guardar-sesion').prop('disabled', false).text('Iniciar Temporizador');
        });
    });

    $('#btn-agregar-consumo').click(function() {
        let ventaId = parseInt($('#cuenta-venta-id').val());
        let btn = $(this);
        btn.prop('disabled', true).text('...');

        $.post(`/api/ventas/${ventaId}/consumo`, {
            producto_id: $('#productoConsumo').val(),
            cantidad: $('#cantidadConsumo').val()
        }, function() {
            cargarTablero().done(function() {
                abrirCuenta(ventaId);
                $('#cantidadConsumo').val(1);
                btn.prop('disabled', false).text('+ Agregar a la Cuenta');
            });
        });
    });

    $('#btn-registrar-pago').off('click').on('click', function() {
        let ventaId = parseInt($('#cuenta-venta-id').val());
        let monto = parseFloat($('#montoPago').val());
        let deudaActual = parseFloat($('#cuenta-deuda').text());
        let btn = $(this);

        if(isNaN(monto) || monto <= 0) return alert("Ingresa un monto válido");
        if(monto > deudaActual) return alert(`¡Error! No puedes cobrar S/ ${monto.toFixed(2)} porque la deuda es solo S/ ${deudaActual.toFixed(2)}`);

        btn.prop('disabled', true).text('Procesando...');

        $.ajax({
            url: `/api/ventas/${ventaId}/pago`, type: 'POST',
            data: { monto: monto, metodo: $('#metodoPago').val() },
            success: function() {
                cargarTablero().done(function() {
                    let sesionAunExiste = sesiones.find(s => s.id === ventaId);
                    if (!sesionAunExiste) {
                        alert("✅ Cuenta pagada. Ticket cerrado.");
                        $('#modalCuenta').modal('hide');
                    } else {
                        abrirCuenta(ventaId);
                    }
                    btn.prop('disabled', false).text('Abonar / Pagar');
                });
            },
            error: function(err) {
                alert("❌ " + (err.responseJSON ? err.responseJSON.error : "Ocurrió un error"));
                btn.prop('disabled', false).text('Abonar / Pagar');
            }
        });
    });

    $('#btn-cobrar-todo').click(function() {
        let ventaId = $('#cuenta-venta-id').val();
        let metodo = $('#metodoPago').val();
        if(confirm("¿Liquidar toda la cuenta pendiente?")) {
            $.post(`/api/ventas/${ventaId}/cobrar-todo`, { metodo_pago: metodo }, function() {
                $('#modalCuenta').modal('hide');
                cargarTablero();
            });
        }
    });

    // ---- EVENTOS DE CAJA Y AGENTE ----
    $('#menu-caja').click(function(e) {
        e.preventDefault();
        $('#modalCaja').modal('show');
        consultarCaja();
    });

    $('#btn-abrir-caja').off('click').on('click', function() {
        $.post('/api/caja/abrir', {
            monto_inicial: $('#montoAperturaEfectivo').val(),
            monto_inicial_yape: $('#montoAperturaYape').val()
        }, function() { consultarCaja(); });
    });

    $('#btn-guardar-egreso').off('click').on('click', function() {
        let concepto = $('#conceptoEgreso').val();
        let monto = $('#montoEgreso').val();
        if (!concepto || monto <= 0) return alert("Datos inválidos.");

        $.post('/api/caja/egreso', {
            concepto: concepto, monto: monto, metodo: $('#metodoEgreso').val()
        }, function() {
            $('#conceptoEgreso, #montoEgreso').val('');
            consultarCaja();
        });
    });

    $('#btn-guardar-agente').off('click').on('click', function() {
        let monto = $('#agenteMonto').val();
        let comision = $('#agenteComision').val();
        if (monto <= 0 || comision < 0) return alert("Datos inválidos.");

        $.post('/api/caja/agente', {
            tipo_operacion: $('#agenteTipo').val(), monto: monto, comision: comision
        }, function() {
            $('#agenteMonto, #agenteComision').val('');
            alert("Operación completada. Saldos cruzados.");
            consultarCaja();
        });
    });

    $('#btn-cerrar-caja').click(function() {
        if(confirm("¿Cerrar la bóveda? El sistema se bloqueará para nuevas ventas.")) {
            $.post('/api/caja/cerrar', function() {
                $('#modalCaja').modal('hide');
                cargarTablero();
            });
        }
    });

    // ---- EVENTOS DE INVENTARIO ----
    $('#menu-inventario').click(function(e) {
        e.preventDefault();
        limpiarFormularioProducto();
        $('#modalInventario').modal('show');
    });

    $('#inv-tipo').change(function() {
        if ($(this).val() == '1') {$('#inv-minutos').prop('disabled', false).val(''); }
        else { $('#inv-minutos').prop('disabled', true).val(''); }
    });

    $('#btn-guardar-producto').click(function() {
        let id = $('#inv-id').val();
        let data = {
            nombre: $('#inv-nombre').val(), precio: $('#inv-precio').val(),
            es_tiempo: $('#inv-tipo').val(), minutos: $('#inv-minutos').val()
        };

        if (!data.nombre || data.precio <= 0 || (data.es_tiempo == 1 && data.minutos <= 0)) {
            return alert("Completa los datos correctamente.");
        }

        $(this).prop('disabled', true).text('Guardando...');$.ajax({
            url: id ? `/api/productos/${id}` : '/api/productos',
            type: id ? 'PUT' : 'POST',
            data: data,
            success: function() {
                limpiarFormularioProducto();
                cargarProductos();
            }
        });
    });

    $('#btn-cancelar-edicion').click(function() { limpiarFormularioProducto(); });

    // ---- ANULAR UN TICKET COMPLETO ----
    $('#btn-anular-ticket').off('click').on('click', function() {
        let ventaId = $('#cuenta-venta-id').val();

        if(confirm("🚨 ¿Estás totalmente seguro de ANULAR este ticket? Se borrará del tablero por completo y no se podrá recuperar.")) {
            let btn = $(this);
            btn.prop('disabled', true).text('Borrando...');

            $.ajax({
                url: `/api/ventas/${ventaId}/anular`,
                type: 'DELETE',
                success: function() {
                    $('#modalCuenta').modal('hide');
                    cargarTablero(); // Refresca el tablero para que el ticket desaparezca
                    btn.prop('disabled', false).html('🗑️ Anular Ticket');
                },
                error: function() {
                    alert("Ocurrió un error al intentar anular el ticket.");
                    btn.prop('disabled', false).html('🗑️ Anular Ticket');
                }
            });
        }
    });

});

// ==========================================
// MÓDULO DE HISTORIAL DE VENTAS
// ==========================================
$('#menu-historial').click(function(e) {
    e.preventDefault();
    cargarHistorialVentas();
    $('#modalHistorialVentas').modal('show');
});

function cargarHistorialVentas() {
    $('#lista-ventas-hoy').html('<tr><td colspan="5" class="text-center">Cargando...</td></tr>');
    $.get('/api/ventas/hoy', function(ventas) {
        let html = '';
        if(ventas.length === 0) {
            html = '<tr><td colspan="5" class="text-center text-muted">No hay ventas en este turno</td></tr>';
        } else {
            ventas.forEach(v => {
                let estadoBadge = (v.total <= v.monto_pagado && v.total > 0) || v.estado === 'completado'
                    ? '<span class="badge bg-success">Pagado</span>'
                    : '<span class="badge bg-warning text-dark">Pendiente</span>';

                html += `
                    <tr>
                        <td class="fw-bold text-muted">#${v.id}</td>
                        <td class="fw-bold">${v.nombre_nino || 'Venta Libre'}</td>
                        <td>S/ ${parseFloat(v.total).toFixed(2)}</td>
                        <td>S/ ${parseFloat(v.monto_pagado).toFixed(2)} <br> ${estadoBadge}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-danger fw-bold shadow-sm" onclick="anularVentaDesdeHistorial(${v.id})">Anular Ticket</button>
                        </td>
                    </tr>`;
            });
        }
        $('#lista-ventas-hoy').html(html);
    });
}

window.anularVentaDesdeHistorial = function(id) {
    if(confirm("🚨 ¿Estás seguro de anular este ticket? Si ya lo habías cobrado, el dinero se descontará de la caja y deberás devolverle el Efectivo/Yape al cliente.")) {
        $.ajax({
            url: `/api/ventas/${id}/anular`,
            type: 'DELETE',
            success: function() {
                cargarHistorialVentas(); // Refrescar la tabla del historial
                cargarTablero();         // Refrescar el tablero por si era un ticket activo
                alert("✅ Ticket anulado. Revisa tu Caja, los saldos se han ajustado automáticamente.");
            },
            error: function() {
                alert("No se pudo anular el ticket.");
            }
        });
    }
}

// ---- FINALIZAR JUEGO (Retirar al niño) ----
$('#btn-finalizar-juego').off('click').on('click', function() {
    let ventaId = $('#cuenta-venta-id').val();
    let deudaActual = parseFloat($('#cuenta-deuda').text());

    if (deudaActual > 0) {
        alert("🚨 No puedes finalizar el juego. El cliente aún debe S/ " + deudaActual.toFixed(2));
        return;
    }

    if(confirm("🏁 ¿El tiempo terminó y el niño ya se retira? El ticket desaparecerá del tablero.")) {
        let btn = $(this);
        btn.prop('disabled', true).text('Finalizando...');

        $.ajax({
            url: `/api/ventas/${ventaId}/finalizar`,
            type: 'POST',
            success: function() {
                $('#modalCuenta').modal('hide');
                cargarTablero();
                btn.prop('disabled', false).html('🏁 Finalizar Juego');
            },
            error: function(err) {
                let msj = err.responseJSON ? err.responseJSON.error : "Ocurrió un error.";
                alert("❌ " + msj);
                btn.prop('disabled', false).html('🏁 Finalizar Juego');
            }
        });
    }
});
