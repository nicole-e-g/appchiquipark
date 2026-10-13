<!-- MODAL: Historial de Ventas -->
<div class="modal fade" id="modalHistorialVentas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #581c87; color: white;">
                <h5 class="modal-title fw-bold">📋 Ventas del Turno Actual</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-center">
                        <thead class="table-light">
                        <tr>
                            <th>Ticket #</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Pagado</th>
                            <th>Acción</th>
                        </tr>
                        </thead>
                        <tbody id="lista-ventas-hoy"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
