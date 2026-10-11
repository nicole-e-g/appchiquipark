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
