<!-- MODAL: Gestión de Inventario (Productos) -->
<div class="modal fade" id="modalInventario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-dark">
                <h5 class="modal-title fw-bold">📦 Gestión de Inventario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light">
                <!-- Formulario para Crear / Editar -->
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-body">
                        <h6 class="fw-bold text-secondary mb-3" id="titulo-form-producto">Agregar Nuevo Producto</h6>
                        <input type="hidden" id="inv-id">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Nombre</label>
                                <input type="text" class="form-control" id="inv-nombre" placeholder="Ej: Gaseosa">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Precio (S/)</label>
                                <input type="number" step="0.10" class="form-control" id="inv-precio" value="0.00">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Tipo</label>
                                <select class="form-select" id="inv-tipo">
                                    <option value="0">Consumo (Comida/Bebida)</option>
                                    <option value="1">Tiempo de Juego</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Minutos (Si es tiempo)</label>
                                <input type="number" class="form-control" id="inv-minutos" placeholder="Ej: 30" disabled>
                            </div>
                            <div class="col-12 mt-3 text-end">
                                <button class="btn btn-secondary btn-sm me-2 d-none" id="btn-cancelar-edicion">Cancelar Edición</button>
                                <button class="btn btn-primary fw-bold" id="btn-guardar-producto">Guardar Producto</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Productos -->
                <div class="table-responsive" style="max-height: 300px;">
                    <table class="table table-sm table-hover align-middle bg-white border">
                        <thead class="table-light sticky-top">
                        <tr>
                            <th>Nombre</th>
                            <th>Precio</th>
                            <th>Tipo</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                        </thead>
                        <tbody id="lista-inventario">
                        <!-- Inyectado por JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
