<!-- MODAL: Gestión de Caja -->
<div class="modal fade" id="modalCaja" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold">💰 Control de Caja</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Vista 0: Cargando -->
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

            <!-- Vista 2: Caja Abierta (Versión Avanzada) -->
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

            <!-- Botones del Footer solo cuando la caja está abierta -->
            <div class="modal-footer d-none justify-content-between" id="caja-footer-abierta">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Seguir Trabajando</button>
                <button type="button" class="btn btn-dark fw-bold" id="btn-cerrar-caja">Cerrar Caja (Fin de Turno)</button>
            </div>
        </div>
    </div>
</div>
