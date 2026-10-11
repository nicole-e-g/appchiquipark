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
    if(!sesion) {
        $('#modalCuenta').modal('hide'); // Forzar cierre si la sesión ya no existe
        return;
    }

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
    let estaPagado = (deuda <= 0);
    $('#montoPago').prop('disabled', estaPagado);
    $('#metodoPago').prop('disabled', estaPagado);
    $('#btn-registrar-pago').prop('disabled', estaPagado);
    $('#btn-cobrar-todo').prop('disabled', estaPagado);

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

// 8. Registrar un Pago (Validado y Blindado)
        $('#btn-registrar-pago').off('click').on('click', function() {
            let ventaId = parseInt($('#cuenta-venta-id').val());
            let monto = parseFloat($('#montoPago').val());
            let deudaActual = parseFloat($('#cuenta-deuda').text());
            let btn = $(this);

            if(isNaN(monto) || monto <= 0) {
                alert("Ingresa un monto válido");
                return;
            }

            // Bloqueo visual de sobrepago preventivo
            if(monto > deudaActual) {
                alert("¡Error! No puedes cobrar S/ " + monto.toFixed(2) + " porque la deuda es solo S/ " + deudaActual.toFixed(2));
                return;
            }

            btn.prop('disabled', true).text('Procesando...');

            $.ajax({
                url: `/api/ventas/${ventaId}/pago`,
                type: 'POST',
                data: { monto: monto, metodo: $('#metodoPago').val() },
                success: function(response) {
                    cargarTablero().done(function() {
                        // Verificamos si la sesión sigue existiendo en el tablero activo
                        let sesionAunExiste = sesiones.find(s => s.id === ventaId);

                        if (!sesionAunExiste) {
                            alert("✅ Cuenta pagada por completo. El ticket ha sido cerrado.");
                            $('#modalCuenta').modal('hide');
                        } else {
                            abrirCuenta(ventaId); // Solo actualiza si aún hay deuda
                        }

                        btn.prop('disabled', false).text('Abonar / Pagar');
                    });
                },
                error: function(err) {
                    // Si el backend rechaza el pago, mostramos el error exacto
                    let msjError = err.responseJSON ? err.responseJSON.error : "Ocurrió un error";
                    alert("❌ " + msjError);
                    btn.prop('disabled', false).text('Abonar / Pagar');
                }
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
// Función para consultar si la caja está abierta o cerrada
function consultarCaja() {
    // 1. Mostrar cargando y ocultar todo lo demás
    $('#caja-loading').removeClass('d-none');
    $('#caja-cerrada-view, #caja-abierta-view, #caja-footer-abierta').addClass('d-none');

    $.get('/api/caja/estado', function(data) {
        // 2. Ocultar el spinner de cargando porque ya llegó la respuesta
        $('#caja-loading').addClass('d-none');

        if (data.abierta) {
            // 3A. SI ESTÁ ABIERTA: Mostrar panel y botones de caja abierta
            $('#caja-abierta-view, #caja-footer-abierta').removeClass('d-none');

            $('#lbl-caja-base').text('S/ ' + parseFloat(data.caja.monto_inicial).toFixed(2));
            $('#lbl-caja-ingreso-efectivo').text('S/ ' + parseFloat(data.ingresos_efectivo).toFixed(2));
            $('#lbl-caja-yape').text('S/ ' + parseFloat(data.ingresos_yape).toFixed(2));
            $('#lbl-caja-egresos').text('S/ ' + parseFloat(data.egresos).toFixed(2));
            $('#lbl-caja-efectivo').text(parseFloat(data.efectivo_esperado).toFixed(2));

            // Dibujar historial de egresos
            let htmlEgresos = '';
            if(data.lista_egresos && data.lista_egresos.length > 0) {
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
                htmlEgresos = '<tr><td colspan="3" class="text-center text-muted">No hay egresos registrados</td></tr>';
            }
            $('#lista-historial-egresos').html(htmlEgresos);

        } else {
            // 3B. SI ESTÁ CERRADA: Mostrar panel para abrir la caja (ESTO FALTABA)
            $('#caja-cerrada-view').removeClass('d-none');
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

    // ==========================================
    // MÓDULO DE INVENTARIO Y SELECTS DINÁMICOS
    // ==========================================
    let listaProductos = [];

    // 1. Cargar productos desde la base de datos
    function cargarProductos() {
        $.get('/api/productos', function(data) {
            listaProductos = data;
            dibujarTablaInventario();
            llenarSelectsVentas();
        });
    }

    // 2. Llenar automáticamente los <select> de ventas
    function llenarSelectsVentas() {
        let optionsTiempo = ''; // Solo tiempos (Para Iniciar Sesión)
        let optionsGeneral = ''; // Todo: Tiempos y Comida (Para Agregar Consumo)

        listaProductos.forEach(p => {
            if (p.estado == 1) { // Solo si está activo
                let texto = `${p.nombre} (S/ ${parseFloat(p.precio).toFixed(2)})`;

                // Si es un producto de tiempo, lo ponemos en las opciones de "Iniciar Sesión"
                if (p.es_tiempo == 1) {
                    optionsTiempo += `<option value="${p.id}">${texto}</option>`;
                }

                // TODOS los productos van al modal de "Agregar Consumo"
                optionsGeneral += `<option value="${p.id}">${texto}</option>`;
            }
        });

        $('#productoTiempo').html(optionsTiempo);
        $('#productoConsumo').html(optionsGeneral);
    }

    // 3. Abrir el modal de Inventario
    $('#menu-inventario').click(function(e) {
        e.preventDefault();
        limpiarFormularioProducto();
        $('#modalInventario').modal('show');
    });

    // 4. Dibujar la tabla de inventario
    function dibujarTablaInventario() {
        let html = '';
        listaProductos.forEach(p => {
        let badgeEstado = p.estado == 1
        ? '<span class="badge bg-success">Activo</span>'
        : '<span class="badge bg-danger">Inactivo</span>';

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

    // 5. Control del campo Minutos (Se desactiva si es consumo)
    $('#inv-tipo').change(function() {
        if ($(this).val() == '1') {$('#inv-minutos').prop('disabled', false).val('');
    } else {
        $('#inv-minutos').prop('disabled', true).val('');
    }
    });

    // 6. Guardar o Actualizar Producto
    $('#btn-guardar-producto').click(function() {
        let id = $('#inv-id').val();
        let data = {
        nombre: $('#inv-nombre').val(),
        precio: $('#inv-precio').val(),
        es_tiempo: $('#inv-tipo').val(),
        minutos: $('#inv-minutos').val()
    };

        if (!data.nombre || data.precio <= 0 || (data.es_tiempo == 1 && data.minutos <= 0)) {
        alert("Completa los datos correctamente.");
        return;
    }

        let url = id ? `/api/productos/${id}` : '/api/productos';
        let method = id ? 'PUT' : 'POST';

        $(this).prop('disabled', true).text('Guardando...');

        $.ajax({
        url: url,
        type: method,
        data: data,
        success: function(res) {
        limpiarFormularioProducto();
        cargarProductos(); // Refresca la tabla y los selects de venta automáticamente
    }
    });
    });

    // 7. Cargar datos al formulario para editar
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
        $('#btn-guardar-producto').text('Actualizar Producto').removeClass('btn-primary').addClass('btn-warning');
    }

    // 8. Cancelar edición
    $('#btn-cancelar-edicion').click(function() {
        limpiarFormularioProducto();
    });

    function limpiarFormularioProducto() {
        $('#titulo-form-producto').text('Agregar Nuevo Producto');
        $('#inv-id, #inv-nombre, #inv-minutos').val('');
        $('#inv-precio').val('0.00');
        $('#inv-tipo').val('0').trigger('change');
        $('#btn-cancelar-edicion').addClass('d-none');
        $('#btn-guardar-producto').text('Guardar Producto').removeClass('btn-warning').addClass('btn-primary').prop('disabled', false);
    }

    // 9. Activar / Desactivar Producto (Borrado Lógico)
    window.toggleEstadoProducto = function(id) {
        $.post(`/api/productos/${id}/toggle`, function(res) {
            cargarProductos(); // Actualiza visualmente todo al instante
        });
    }

    // EJECUTAR AL INICIO:
    // Añade la llamada a cargarProductos() justo debajo de donde llamas a cargarTablero() al inicio de tu script.
    cargarProductos();
