<!-- MODAL: Gestión de Caja y Agente -->
<div class="modal fade" id="modalCaja" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold">💼 Bóveda: Caja & Agente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center" id="caja-loading">
                <div class="spinner-border text-dark my-4"></div>
            </div>

            <!-- Vista 1: Apertura Doble -->
            <div class="modal-body text-center d-none" id="caja-cerrada-view">
                <h4 class="text-danger mb-3">La bóveda está cerrada</h4>
                <div class="row w-75 mx-auto">
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Apertura Efectivo (Cajón)</label>
                        <input type="number" class="form-control text-center" id="montoAperturaEfectivo" value="0">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-primary">Apertura Yape (Celular)</label>
                        <input type="number" class="form-control text-center" id="montoAperturaYape" value="0">
                    </div>
                </div>
                <button class="btn btn-success w-75 fw-bold" id="btn-abrir-caja">Abrir Turno</button>
            </div>

            <!-- Vista 2: Caja Abierta -->
            <div class="modal-body d-none bg-light" id="caja-abierta-view">

                <!-- Resumen de las 2 Cajas -->
                <div class="row text-center mb-4 g-2">
                    <div class="col-md-6">
                        <div class="card border-success shadow-sm">
                            <div class="card-header bg-success text-white fw-bold py-1">💵 Efectivo en Cajón</div>
                            <div class="card-body fs-3 fw-bold text-success">S/ <span id="lbl-caja-efectivo">0.00</span></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-primary shadow-sm">
                            <div class="card-header bg-primary text-white fw-bold py-1">📱 Saldo en Yape/Plin</div>
                            <div class="card-body fs-3 fw-bold text-primary">S/ <span id="lbl-caja-yape">0.00</span></div>
                        </div>
                    </div>
                </div>

                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation"><button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#tab-gastos">🛒 Gastos</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link fw-bold text-primary" data-bs-toggle="tab" data-bs-target="#tab-agente">🔄 Operaciones Agente</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link fw-bold text-secondary" data-bs-toggle="tab" data-bs-target="#tab-historial">📋 Historial</button></li>
                </ul>

                <div class="tab-content bg-white border border-top-0 p-3 mb-3">
                    <!-- Tab Gastos -->
                    <div class="tab-pane fade show active" id="tab-gastos">
                        <div class="row g-2">
                            <div class="col-5"><input type="text" class="form-control" id="conceptoEgreso" placeholder="¿Qué se compró?"></div>
                            <div class="col-3"><input type="number" class="form-control" id="montoEgreso" placeholder="Monto"></div>
                            <div class="col-4">
                                <div class="input-group">
                                    <select class="form-select" id="metodoEgreso">
                                        <option value="efectivo">Cajón</option>
                                        <option value="yape">Yape</option>
                                    </select>
                                    <button class="btn btn-danger" id="btn-guardar-egreso">Gastar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Agente -->
                    <div class="tab-pane fade" id="tab-agente">
                        <div class="row g-2 align-items-end">
                            <div class="col-5">
                                <label class="small fw-bold">Tipo de Operación</label>
                                <select class="form-select" id="agenteTipo">
                                    <option value="retiro">El cliente NECESITA Efectivo (Te Yapea)</option>
                                    <option value="deposito">El cliente TRAE Efectivo (Le Yapeas)</option>
                                </select>
                            </div>
                            <div class="col-3">
                                <label class="small fw-bold">Monto Base</label>
                                <input type="number" class="form-control" id="agenteMonto" placeholder="Ej: 100">
                            </div>
                            <div class="col-2">
                                <label class="small fw-bold">Comisión</label>
                                <input type="number" class="form-control" id="agenteComision" placeholder="Ej: 2">
                            </div>
                            <div class="col-2">
                                <button class="btn btn-primary w-100" id="btn-guardar-agente">Procesar</button>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Historial -->
                    <div class="tab-pane fade" id="tab-historial">
                        <div class="table-responsive" style="max-height: 150px;">
                            <table class="table table-sm table-hover align-middle mb-0 text-start">
                                <tbody id="lista-historial-movimientos"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer d-none justify-content-between" id="caja-footer-abierta">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar Ventana</button>
                <button type="button" class="btn btn-dark fw-bold" id="btn-cerrar-caja">Fin de Turno (Cerrar Bóveda)</button>
            </div>
        </div>
    </div>
</div>
