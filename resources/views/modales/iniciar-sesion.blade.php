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
